<?php

namespace MobileCart\CoreBundle\EventListener\Security;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\CartComponent\Cart;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class Login implements AuthenticationSuccessHandlerInterface
{
    protected $httpUtils;
    protected $options;
    protected $providerKey;
    protected $defaultOptions = array(
        'always_use_default_target_path' => false,
        'default_target_path' => '/',
        'login_path' => '/login',
        'target_path_parameter' => '_target_path',
        'use_referer' => false,
    );

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    protected $reloadCart = true;

    /**
     * Constructor.
     *
     * @param HttpUtils $httpUtils
     * @param array     $options   Options for processing a successful authentication attempt.
     */
    public function __construct(HttpUtils $httpUtils, array $options = array())
    {
        $this->httpUtils = $httpUtils;
        $this->setOptions($options);
    }

    /**
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $reloadCart
     * @return $this
     */
    public function setReloadCart($reloadCart)
    {
        $this->reloadCart = $reloadCart;
        return $this;
    }

    /**
     * @return bool
     */
    public function getReloadCart()
    {
        return $this->reloadCart;
    }

    /**
     * @param $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();
        $class = get_class($user);

        $event = new CoreEvent();
        $event->setUser($user);

        if ($class === $this->getEntityService()->getRepository(EntityConstants::CUSTOMER)->getClassName()) {

            $user = $this->getEntityService()->find(EntityConstants::CUSTOMER, $token->getUser()->getId());

            if ($user->getDefaultLocale()) {
                $this->getCartSessionService()->getSession()->set('_locale', $user->getDefaultLocale());
            }

            $aCart = $this->getCartSessionService()->getCart();
            if ($this->getReloadCart()
                && !$aCart->hasItems()
            ) {

                $currentCart = $this->getEntityService()->findOneBy(EntityConstants::CART, [
                    'customer' => $user->getId(),
                ]);

                if ($currentCart) {

                    $aCart = new Cart();
                    $aCart->importJson($currentCart->getJson());

                    $this->getCartSessionService()
                        ->setCart($aCart);
                }
            }

            $this->getCartSessionService()
                ->collectShippingMethods()
                ->collectTotals();

            $event->setIsCustomer(1);

            $this->getCartSessionService()
                ->setCustomerEntity($user);

        } else if ($class === $this->getEntityService()->getRepository(EntityConstants::ADMIN_USER)->getClassName()) {
            $user = $this->getEntityService()->find(EntityConstants::ADMIN_USER, $token->getUser()->getId());
            $event->setIsAdmin(1);

            // might as well create a blank cart for admin
            //  this prevents bugs while testing shopping cart also
            $aCart = new Cart();
            $this->getCartSessionService()
                ->setCart($aCart);
        }

        $user->setFailedLogins(0)
            ->setLastLoginAt(new \DateTime('now'));

        if (!$user->getApiKey()) {
            $user->setApiKey(sha1(microtime()));
        }

        $this->getEntityService()->persist($user);

        $event->setUser($user)
            ->setReturnData($user->getData());

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::LOGIN_SUCCESS, $event);

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {
            return new JsonResponse(array_merge(['success' => 1], $event->getReturnData()));
        }

        return $this->httpUtils->createRedirectResponse($request, $this->determineTargetUrl($request));
    }

    /**
     * Gets the options.
     *
     * @return array An array of options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the options.
     *
     * @param array $options An array of options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->defaultOptions, $options);
    }

    /**
     * Get the provider key.
     *
     * @return string
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }

    /**
     * Set the provider key.
     *
     * @param string $providerKey
     */
    public function setProviderKey($providerKey)
    {
        $this->providerKey = $providerKey;
    }

    /**
     * Builds the target URL according to the defined options.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function determineTargetUrl(Request $request)
    {
        if ($this->options['always_use_default_target_path']) {
            return $this->options['default_target_path'];
        }

        if ($targetUrl = $request->get($this->options['target_path_parameter'], null, true)) {
            return $targetUrl;
        }

        if (null !== $this->providerKey && $targetUrl = $request->getSession()->get('_security.'.$this->providerKey.'.target_path')) {
            $request->getSession()->remove('_security.'.$this->providerKey.'.target_path');

            return $targetUrl;
        }

        if ($this->options['use_referer'] && ($targetUrl = $request->headers->get('Referer')) && $targetUrl !== $this->httpUtils->generateUri($request, $this->options['login_path'])) {
            return $targetUrl;
        }

        return $this->options['default_target_path'];
    }
}
