<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutSubmitOrderReturn
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutSubmitOrderReturn
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @param \MobileCart\CoreBundle\Service\OrderService $orderService
     * @return $this
     */
    public function setOrderService(\MobileCart\CoreBundle\Service\OrderService $orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
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
    public function onCheckoutSubmitOrder(CoreEvent $event)
    {
        if ($event->getSuccess()) {

            // NOTE: for paypal redirects and similar,
            //  an event listener which runs before this should set the redirect_url value

            $redirectUrl = $event->get('redirect_url', '')
                ? $event->get('redirect_url', '')
                : $this->getRouter()->generate('cart_checkout_success', []);

            $event->setReturnData('redirect_url', $redirectUrl);

            // only set the value in session if we're using the session
            if (!$this->getCartService()->getIsApiRequest()) {

                $this->getCartService()
                    ->resetCart()
                    ->getSession()
                    ->set('order_id', $this->getOrderService()->getOrder()->getId());
            }
        }

        $event->setReturnData('messages', $event->getMessages());
        $event->setResponse(new JsonResponse($event->getReturnData()));
    }
}
