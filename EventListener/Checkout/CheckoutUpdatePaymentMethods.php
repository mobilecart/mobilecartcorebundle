<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;

/**
 * Class CheckoutUpdatePaymentMethods
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdatePaymentMethods
{

    protected $formFactory;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param $checkoutSession
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    /**
     * @param $paymentService
     * @return $this
     */
    public function setPaymentService($paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\PaymentService
     */
    public function getPaymentService()
    {
        return $this->paymentService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutUpdateBillingAddress(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
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
