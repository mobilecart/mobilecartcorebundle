<?php

namespace MobileCart\CoreBundle\Payment;

interface PaymentMethodServiceInterface
{

    public function getCode();

    public function getLabel();

    public function setFormFactory($formFactory);

    public function getFormFactory();

    public function buildForm();

    public function setForm($form);

    public function getForm();

    public function canAuthorize();

    public function canCapture();

    public function setEnableAuthorize($yesNo);

    public function getEnableAuthorize();

    public function setEnableCaptureOnInvoice($yesNo);

    public function getEnableCaptureOnInvoice();

    public function setEnableCaptureOnShipment($yesNo);

    public function getEnableCaptureOnShipment();

    public function setIsTestMode($isTestMode);

    public function getIsTestMode();

    public function setIsSubmission($isSubmission);

    public function getIsSubmission();

    public function setPaymentData($paymentData);

    public function getPaymentData();

    public function setOrderData($orderData);

    public function getOrderData();

    public function buildGatewayRequest();

    public function setGatewayRequest($gatewayRequest);

    public function getGatewayRequest();

    public function sendGatewayRequest();

    public function setGatewayResponse($gatewayResponse);

    public function getGatewayResponse();

    public function authorize();

    public function setIsAuthorized($yesNo);

    public function getIsAuthorized();

    public function capture();

    public function setIsCaptured($isCaptured);

    public function getIsCaptured();
}
