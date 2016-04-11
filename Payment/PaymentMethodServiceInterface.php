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

    public function canRefund();

    /**
     * Can create a re-usable token
     *
     * @return mixed
     */
    public function canTokenPayment();

    /**
     * Can subscribe to an automated recurring billing service/subscription
     *
     * @return mixed
     */
    public function canSubscribeRecurring();

    public function setEnableAuthorize($yesNo);

    public function getEnableAuthorize();

    public function setEnableCapture($yesNo);

    public function getEnableCapture();

    public function setEnableTokenPayment($yesNo);

    public function getEnableTokenPayment();

    public function setEnableSubscribeRecurring($yesNo);

    public function getEnableSubscribeRecurring();

    public function setIsTestMode($isTestMode);

    public function getIsTestMode();

    public function setIsSubmission($isSubmission);

    public function getIsSubmission();

    public function setPaymentData($paymentData);

    public function getPaymentData();

    public function setOrderPaymentData($orderPaymentData);

    public function getOrderPaymentData();

    public function setOrderData($orderData);

    public function getOrderData();

    public function buildGatewayRequest();

    public function setGatewayRequest($gatewayRequest);

    public function getGatewayRequest();

    public function sendGatewayRequest();

    public function setGatewayResponse($gatewayResponse);

    public function getGatewayResponse();

    public function buildTokenPaymentRequest();

    public function setTokenPaymentRequest($tokenPaymentRequest);

    public function getTokenPaymentRequest();

    public function sendTokenPaymentRequest();

    public function setTokenPaymentResponse($tokenPaymentResponse);

    public function getTokenPaymentResponse();

    public function buildSubscribeRecurringRequest();

    public function setSubscribeRecurringRequest($subscribeRecurringRequest);

    public function getSubscribeRecurringRequest();

    public function sendSubscribeRecurringRequest();

    public function setSubscribeRecurringResponse($subscribeRecurringResponse);

    public function getSubscribeRecurringResponse();

    public function authorize();

    public function setIsAuthorized($yesNo);

    public function getIsAuthorized();

    public function capture();

    public function setIsCaptured($isCaptured);

    public function getIsCaptured();

    public function setConfirmation($confirmation);

    public function getConfirmation();

    public function setCcLastFour($ccLastFour);

    public function getCcLastFour();

    public function setCcType($ccType);

    public function getCcType();

    public function setFingerprint($fingerprint);

    public function getFingerprint();
}
