<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class UpdateTotalsShipping
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class UpdateTotalsShipping
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onUpdateTotalsShipping(CoreEvent $event)
    {
        if ($event->getRequest()->getSession() && $event->getMessages()) {
            foreach($event->getMessages() as $code => $messages) {
                if (!$messages) {
                    continue;
                }
                foreach($messages as $message) {
                    $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                }
            }
        }

        if (!$event->getReturnData('success', false)) {
            $event->setResponse(new RedirectResponse($this->getRouter()->generate('cart_view', [])));
            return;
        }

        if (is_array($event->getRecollectShipping())) {
            $this->getCartService()->collectAddressShipments($event->getRecollectShipping());
        }

        $this->getCartService()->saveCart();
        $event->setReturnData('cart', $this->getCartService()->getCart());

        switch($event->get('format')) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $event->setResponse(new RedirectResponse($this->getRouter()->generate('cart_view', [])));
                break;
        }
    }
}
