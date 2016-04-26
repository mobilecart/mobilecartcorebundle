<?php

namespace MobileCart\CoreBundle\EventListener\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface;
use Symfony\Component\EventDispatcher\Event;

class PaymentMethodHandler
{

    protected $paymentMethodService;

    protected $entityService;

    protected $cartSessionService;

    protected $isEnabled;

    public function setPaymentMethodService($paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
        return $this;
    }

    public function getPaymentMethodService()
    {
        return $this->paymentMethodService;
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
     * @return mixed
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Event Listener : top-level logic happens here
     *  build a request, handle the request, handle the response
     *
     * @param Event $event
     * @return mixed
     */
    public function onPaymentMethodCollect(Event $event)
    {
        if (!$this->getIsEnabled()) {
            return false;
        }

        // trying to be more secure by not passing the full service into the view
        //  so , getting the service requires a flag to be set
        if ($event->getFindService()) {
            if ($event->getCode() == $this->getPaymentMethodService()->getCode()) {
                $event->setService($this->getPaymentMethodService());
                return true; // makes no difference
            }

            return false;
        }

        // todo : handle is_backend, is_frontend

        $paymentMethodService = $this->getPaymentMethodService();

        if (
            $paymentMethodService->supportsAction(PaymentMethodServiceInterface::ACTION_PURCHASE_STORED_TOKEN)
            && $this->getCartSessionService()
            && $this->getCartSessionService()->getCustomerId()
        ) {

            $customerTokens = $this->getEntityService()->findBy(EntityConstants::CUSTOMER_TOKEN, [
                'customer' => $this->getCartSessionService()->getCustomerId(),
                'service' => $paymentMethodService->getCode(),
            ]);

            if ($customerTokens) {
                $paymentMethodService->setCustomerTokens($customerTokens);
                $paymentMethodService->setAction(PaymentMethodServiceInterface::ACTION_PURCHASE_STORED_TOKEN);
            }

        }

        $form = $paymentMethodService->buildForm()
            ->getForm()
            ->createView();

        // todo: use a class which extends ArrayWrapper

        // trying to be more secure by not passing the full service into the view
        $wrapper = new ArrayWrapper();
        $wrapper->set('code', $paymentMethodService->getCode())
            ->set('label', $paymentMethodService->getLabel())
            ->set('action', $paymentMethodService->getAction())
            ->set('form', $form);

        // payment form requirements
        // * dont conflict with parent form
        // * build form, populate if needed
        // * display using correct input parameters

        $event->addMethod($wrapper);
    }
}
