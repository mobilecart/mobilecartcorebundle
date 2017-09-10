<?php

namespace MobileCart\CoreBundle\EventListener\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\Payment\FilterPaymentMethodCollectEvent;
use MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface;

/**
 * Class PaymentMethodHandler
 * @package MobileCart\CoreBundle\EventListener\Payment
 */
class PaymentMethodHandler
{
    /**
     * @var \MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface
     */
    protected $paymentMethodService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @var bool
     */
    protected $isEnabled;

    /**
     * @param PaymentMethodServiceInterface $paymentMethodService
     * @return $this
     */
    public function setPaymentMethodService(PaymentMethodServiceInterface $paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
        return $this;
    }

    /**
     * @return PaymentMethodServiceInterface
     */
    public function getPaymentMethodService()
    {
        return $this->paymentMethodService;
    }

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
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Event Listener : top-level logic happens here
     *  build a request, handle the request, handle the response
     *
     * @param FilterPaymentMethodCollectEvent $event
     * @return mixed
     */
    public function onPaymentMethodCollect(FilterPaymentMethodCollectEvent $event)
    {
        if (!$this->getIsEnabled()) {
            return false;
        }

        $methodRequest = $event->getCollectPaymentMethodRequest();
        $paymentMethodService = $this->getPaymentMethodService();

        $customerTokens = $this->getEntityService()->findBy(EntityConstants::CUSTOMER_TOKEN, [
            'customer' => $this->getCartSessionService()->getCustomerId(),
            'service' => $paymentMethodService->getCode(),
        ]);

        if ($customerTokens) {
            $paymentMethodService->setCustomerTokens($customerTokens);
        }

        if ($methodRequest->getAction()) {

            if ($paymentMethodService->supportsAction($methodRequest->getAction())) {
                $paymentMethodService->setAction($methodRequest->getAction());
            } else {
                return false;
            }

            $this->getPaymentMethodService()->setAction($methodRequest->getAction());
        } else {

            if ($customerTokens
                && $this->getPaymentMethodService()->supportsAction(PaymentMethodServiceInterface::ACTION_PURCHASE_STORED_TOKEN)
            ) {
                $this->getPaymentMethodService()->setAction(PaymentMethodServiceInterface::ACTION_PURCHASE_STORED_TOKEN);
            }
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

        /**
         * Main form builder logic
         */
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

        if ($methodRequest->getExternalPlanId()) {
            $wrapper->set('external_plan_id', $methodRequest->getExternalPlanId());
        }

        // payment form requirements
        // * dont conflict with parent form
        // * build form, populate if needed
        // * display using correct input parameters

        $event->addMethod($wrapper);
    }
}
