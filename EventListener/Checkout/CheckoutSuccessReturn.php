<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CheckoutSuccessReturn
{
    protected $event;

    protected $checkoutSessionService;

    protected $themeService;

    protected $entityService;

    protected $router;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function onCheckoutSuccessReturn(Event $event)
    {
        $orderId = $this->getCheckoutSessionService()->getCartSessionService()->getSession()->get('order_id', 0);
        if (!$orderId) {
            // redirect to checkout page
            $url = $this->getRouter()->generate('cart_checkout', []);
            $response = new RedirectResponse($url);
            $event->setResponse($response);
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        // get cart customer
        $cartCustomer = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCart()
            ->getCustomer();

        // clear cart and set customer
        $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->resetCart()
            ->setCustomer($cartCustomer);

        $order = $this->getEntityService()->find(EntityConstants::ORDER, $orderId);

        $returnData['order'] = $order;

        $response = $this->getThemeService()
            ->render('frontend', 'Checkout:success.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
