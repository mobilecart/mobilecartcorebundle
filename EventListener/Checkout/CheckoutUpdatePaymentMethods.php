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
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
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
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
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
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getCheckoutSessionService()->getCartService();
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
        $checkoutSession = $this->getCheckoutSessionService();
        $cartCustomer = $checkoutSession->getCartService()->getCustomer();

        if ($checkoutSession->getIsValidBillingAddress()) {

            $methodRequest = new CollectPaymentMethodRequest();
            $methodRequest->setRegion($cartCustomer->getBillingRegion())
                ->setPostcode($cartCustomer->getBillingPostcode())
                ->setCountryId($cartCustomer->getBillingCountryId());

            $this->getPaymentService()->collectPaymentMethods($methodRequest);
        }
    }
}
