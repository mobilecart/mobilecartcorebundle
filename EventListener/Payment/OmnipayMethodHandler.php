<?php

namespace MobileCart\CoreBundle\EventListener\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Payment\Payment;
use MobileCart\CoreBundle\Payment\GatewayResponse;
use Symfony\Component\EventDispatcher\Event;

class OmnipayMethodHandler
{

    protected $paymentMethodService;

    protected $is_enabled;

    public function setPaymentMethodService($paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
        return $this;
    }

    public function getPaymentMethodService()
    {
        return $this->paymentMethodService;
    }

    public function setIsEnabled($isEnabled)
    {
        $this->is_enabled = $isEnabled;
        return $this;
    }

    public function getIsEnabled()
    {
        return $this->is_enabled;
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

        if ($event->getHandlePayment()) {

            // check code, only handle if it matches
            if ($this->getPaymentMethodService()->getCode() != $event->getCode()) {
                return false;
            }

            $paymentData = $event->getPaymentData();
            $orderData = $event->getOrderData();

            $gatewayResponse = $this->getPaymentMethodService()
                ->setOrderData($orderData)
                ->setPaymentData($paymentData)
                ->capture()
                ->getGatewayResponse();

            $gatewayRequest = $this->getPaymentMethodService()
                ->getGatewayRequest();

            // return a "formal" object for payment information handling
            $payment = new Payment();
            $payment->set('is_successful', $gatewayResponse->isSuccessful())
                ->set('code', $this->getCode())
                ->set('label', $this->getLabel())
                ->set('base_currency', $orderData['base_currency'])
                ->set('base_amount', $orderData['base_total'])
                ->set('currency', $orderData['currency'])
                ->set('amount', $orderData['total'])
                ->set('confirmation', '') // eg confirmation number
                ->set('is_refund', 0);

            $response = new GatewayResponse();
            $response->setResponse($gatewayResponse)
                ->setRequest($gatewayRequest)
                ->setPayment($payment)
                ->setSuccess($gatewayResponse->isSuccessful());

            // todo : check for logger

            // set response
            $event->setResponse($response);

            // stop propogation
            $event->stopPropagation();

        } else {

            $paymentMethodService = $this->getPaymentMethodService();

            $form = $paymentMethodService->buildForm()
                ->getForm()
                ->createView();

            // todo: use a class which extends ArrayWrapper

            // trying to be more secure by not passing the full service into the view
            $wrapper = new ArrayWrapper();
            $wrapper->set('code', $paymentMethodService->getCode())
                ->set('label', $paymentMethodService->getLabel())
                ->set('form', $form);

            // payment form requirements
            // * dont conflict with parent form
            // * build form, populate if needed
            // * display using correct input parameters

            $event->addMethod($wrapper);
        }
    }
}
