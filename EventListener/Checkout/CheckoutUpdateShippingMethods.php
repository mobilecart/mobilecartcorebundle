<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

use MobileCart\CoreBundle\Shipping\RateRequest;

class CheckoutUpdateShippingMethods
{
    protected $event;

    protected $formFactory;

    protected $checkoutSessionService;

    protected $shippingService;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
    {
        return $this->shippingService;
    }

    public function onCheckoutUpdateShippingAddress(Event $event)
    {
        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $checkoutSession = $this->getCheckoutSessionService();

        if (!$event->getIsSame()
            && $checkoutSession->getIsValidShippingAddress()
        ) {
            // only updating the main address
            $this->getCheckoutSessionService()->getCartSessionService()->collectShippingMethods('main');
            $event->setReturnData($returnData);
        }

        $event->setReturnData($returnData);
    }
}
