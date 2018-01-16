<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class GuardTokenAuthService
 * @package MobileCart\CoreBundle\Service
 */
class GuardTokenAuthService extends AbstractGuardAuthenticator
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var bool
     */
    protected $allowAdminLogin = false;

    /**
     * @return AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param CartService $cartService
     * @return $this
     */
    public function setCartService(CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param $isAllowed
     * @return $this
     */
    public function setAllowAdminLogin($isAllowed)
    {
        $this->allowAdminLogin = $isAllowed;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowAdminLogin()
    {
        return $this->allowAdminLogin;
    }

    /**
     * Called on every request. Return whatever credentials you want,
     *  or null to stop authentication.
     */
    public function getCredentials(Request $request)
    {
        if (!$token = $request->headers->get('X-AUTH-TOKEN')) {
            // no token? Return null and no other methods will be called
            return;
        }

        // What you return here will be passed to getUser() as $credentials
        return [
            'token' => $token,
        ];
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return mixed|null|UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiKey = $credentials['token'];

        $this->getCartService()->setIsApiRequest(true);

        // if null, authentication will fail
        // if a User object, checkCredentials() is called

        $customer = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
            'api_key' => $apiKey,
        ]);

        if ($customer) {
            $this->getCartService()->setCustomerEntity($customer);
            return $customer;
        }

        if (!$this->getAllowAdminLogin()) {
            return null;
        }

        $adminUser = $this->getEntityService()->findOneBy(EntityConstants::ADMIN_USER, [
            'api_key' => $apiKey,
        ]);

        if ($adminUser) {
            $this->getCartService()->setIsAdminUser(true);
            return $adminUser;
        }

        return null;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case

        // return true to cause authentication success
        return true;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * @param Request $request
     * @param AuthenticationException $exception
     * @return null|JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            'success' => false,
            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, 403);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required',
            'success' => false,
        ];

        return new JsonResponse($data, 401);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
