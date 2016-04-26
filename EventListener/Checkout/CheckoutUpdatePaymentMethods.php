<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;

class CheckoutUpdatePaymentMethods
{
    protected $event;

    protected $formFactory;

    protected $checkoutSessionService;

    protected $paymentService;

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

    public function setPaymentService($paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

    public function getPaymentService()
    {
        return $this->paymentService;
    }

    public function onCheckoutUpdateBillingAddress(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $checkoutSession = $this->getCheckoutSessionService();

        $cartCustomer = $checkoutSession->getCartSessionService()->getCustomer();

        if ($checkoutSession->getIsValidBillingAddress()) {

            $methodRequest = new CollectPaymentMethodRequest();
            $methodRequest->setRegion($cartCustomer->getBillingRegion())
                ->setPostcode($cartCustomer->getBillingPostcode())
                ->setCountryId($cartCustomer->getBillingCountryId());

            $this->getPaymentService()->collectPaymentMethods($methodRequest);
        }

        $event->setReturnData($returnData);
    }
}
