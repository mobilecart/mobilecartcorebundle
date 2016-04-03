<?php

namespace MobileCart\CoreBundle\Payment\Method\Dummy;

use Omnipay\Dummy\Gateway; // composer package : omnipay/dummy

use MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface;

/**
 * Class DummyPaymentService
 * @package MobileCart\CoreBundle\Payment\Method\Dummy
 */
class DummyPaymentService
    implements PaymentMethodServiceInterface
{
    protected $formFactory;

    protected $form;

    protected $code = 'dummy';

    protected $label = 'Test Method';

    protected $isTestMode = false;

    protected $enableAuthorize = false;

    protected $enableCaptureOnInvoice = false;

    protected $enableCaptureOnShipment = false;

    protected $paymentData = [];

    protected $orderData = [];

    protected $isAuthorized = false;

    protected $isCaptured = false;

    protected $gatewayRequest;

    protected $gatewayResponse;

    /**
     * @param $formFactory
     * @return $this
     */
    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableAuthorize($yesNo)
    {
        $isEnabled = ($yesNo != '0' && $yesNo != 'false');
        $this->enableAuthorize = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableAuthorize()
    {
        return $this->enableAuthorize;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableCaptureOnInvoice($yesNo)
    {
        $isEnabled = ($yesNo != '0' && $yesNo != 'false');
        $this->enableCaptureOnInvoice = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableCaptureOnInvoice()
    {
        return $this->enableCaptureOnInvoice;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableCaptureOnShipment($yesNo)
    {
        $isEnabled = ($yesNo != '0' && $yesNo != 'false');
        $this->enableCaptureOnShipment = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableCaptureOnShipment()
    {
        return $this->enableCaptureOnShipment;
    }

    /**
     * @return $this
     */
    public function buildForm()
    {
        $formType = new FormDummyType();
        $form = $this->getFormFactory()->create($formType);
        $this->setForm($form);
        return $this;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return bool
     */
    public function canAuthorize()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canCapture()
    {
        return true;
    }

    /**
     * @param $isTestMode
     * @return $this
     */
    public function setIsTestMode($isTestMode)
    {
        $this->isTestMode = ($isTestMode != '0' && $isTestMode != 'false');
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsTestMode()
    {
        return $this->isTestMode;
    }

    /**
     * @param $paymentData
     * @return $this
     */
    public function setPaymentData($paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * @param $orderData
     * @return $this
     */
    public function setOrderData($orderData)
    {
        $this->orderData = $orderData;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderData()
    {
        return $this->orderData;
    }

    /**
     * @return $this
     */
    public function buildGatewayRequest()
    {
        $orderData = $this->getOrderData();
        $paymentData = $this->getPaymentData();

        $amount = $orderData['total'];
        $currency = $orderData['currency'];

        $this->setGatewayRequest([
            'amount' => $amount,
            'currency' => $currency,
            'card' => $paymentData,
        ]);

        return $this;
    }

    /**
     * @param $request
     * @return $this
     */
    public function setGatewayRequest($request)
    {
        $this->gatewayRequest = $request;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGatewayRequest()
    {
        return $this->gatewayRequest;
    }

    /**
     * @return $this
     */
    public function sendGatewayRequest()
    {
        $gateway = new Gateway();
        $gatewayResponse = $gateway->purchase($this->getGatewayRequest())->send();
        $this->setGatewayResponse($gatewayResponse);
        return $this;
    }

    /**
     * @param $gatewayResponse
     * @return $this
     */
    public function setGatewayResponse($gatewayResponse)
    {
        $this->gatewayResponse = $gatewayResponse;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGatewayResponse()
    {
        return $this->gatewayResponse;
    }

    /**
     * @return $this
     */
    public function authorize()
    {
        $this->setIsAuthorized(1);
        return $this;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsAuthorized($yesNo)
    {
        $this->isAuthorized = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAuthorized()
    {
        return $this->isAuthorized;
    }

    /**
     * @return $this
     */
    public function capture()
    {
        $this->buildGatewayRequest()
            ->sendGatewayRequest();

        /** @var \Omnipay\Common\Message\ResponseInterface $gatewayResponse */
        $gatewayResponse = $this->getGatewayResponse();

        $this->setIsCaptured($gatewayResponse->isSuccessful());

        // todo : check for logger

        return $this;
    }

    /**
     * @param $isCaptured
     * @return $this
     */
    public function setIsCaptured($isCaptured)
    {
        $this->isCaptured = $isCaptured;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCaptured()
    {
        return $this->isCaptured;
    }
}
