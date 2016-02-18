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

        //$request = $event->getRequest();
        //$formType = $event->getForm();
        //$entity = $event->getEntity();

        $checkoutSession = $this->getCheckoutSessionService();

        $cartCustomer = $checkoutSession->getCartSessionService()->getCustomer();

        if ($checkoutSession->getIsValidShippingAddress()) {

            $rateRequest = new RateRequest();
            $rateRequest->setRegion($cartCustomer->getShippingRegion())
                ->setPostcode($cartCustomer->getShippingPostcode())
                ->setCountryId($cartCustomer->getShippingCountryId());

            $shippingRates = $this->getShippingService()->collectShippingRates($rateRequest);

            $checkoutSession->getCartSessionService()
                ->setRates($shippingRates);

            // add rates to response
            $returnData['shipping_rates'] = $shippingRates;

            // todo: ensure a valid shipping method is set

            $event->setReturnData($returnData);
        }

        $event->setReturnData($returnData);
    }
}
