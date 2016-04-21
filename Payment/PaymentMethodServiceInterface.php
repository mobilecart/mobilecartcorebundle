<?php

namespace MobileCart\CoreBundle\Payment;

interface PaymentMethodServiceInterface
{

    const ACTION_AUTHORIZE = 'authorize'; // authorize a payment, and store value of token in order.payment_authorize

    const ACTION_CAPTURE = 'capture'; // capture a pre-authorized payment, using value of order.payment_authorize

    const ACTION_AUTHORIZE_AND_CAPTURE = 'authorize_and_capture'; // authorize and capture, using value of order.payment_authorize

    const ACTION_PURCHASE = 'purchase'; // single-use purchase , nothing is stored for future payments

    const ACTION_CREATE_TOKEN = 'create_token'; // create token and store data in CustomerToken entity

    const ACTION_PURCHASE_STORED_TOKEN = 'purchase_stored_token'; // use data in CustomerToken entity

    const ACTION_PURCHASE_AND_SUBSCRIBE_RECURRING = 'purchase_and_subscribe_recurring'; // automatic 3rd party billing/subscription

    const ACTION_AUTHORIZE_REDIRECT = 'authorize_redirect'; // example: paypal redirect . use value of order.payment_authorize

    const ACTION_PURCHASE_CALLBACK = 'purchase_callback'; // example: paypal IPN . use value of order.payment_authorize

    public function setDefaultAction($action);

    public function getDefaultAction();

    public function supportsActions();

    public function supportsAction($action);

    public function setAction($action);

    public function getAction();

    public function getCode();

    public function getLabel();

    public function setFormFactory($formFactory);

    public function getFormFactory();

    public function buildForm();

    public function setForm($form);

    public function getForm();

    public function setIsTestMode($isTestMode);

    public function getIsTestMode();

    public function setIsRefund($isRefund);

    public function getIsRefund();

    public function setIsSubmission($isSubmission);

    public function getIsSubmission();

    public function setPaymentData($paymentData);

    public function getPaymentData();

    public function extractOrderPaymentData();

    public function setOrderData($orderData);

    public function getOrderData();

    public function setConfirmation($confirmation);

    public function getConfirmation();

    public function setCcLastFour($ccLastFour);

    public function getCcLastFour();

    public function setCcType($ccType);

    public function getCcType();

    public function setCcFingerprint($fingerprint);

    public function getCcFingerprint();

    /**
     * Authorize a payment without capturing
     *  Requires an authorization code to be saved on order.payment_authorize
     *
     * @return mixed
     */
    public function authorize();

    public function buildAuthorizeRequest();

    public function setAuthorizeRequest($gatewayRequest);

    public function getAuthorizeRequest();

    public function sendAuthorizeRequest();

    public function setAuthorizeResponse($gatewayResponse);

    public function getAuthorizeResponse();

    public function setIsAuthorized($isAuthorized);

    public function getIsAuthorized();

    /**
     * Capture a pre-authorized payment transaction
     *  Requires an authorization code to be saved on order.payment_authorize
     *
     * @return mixed
     */
    public function capture();

    public function buildCaptureRequest();

    public function setCaptureRequest($gatewayRequest);

    public function getCaptureRequest();

    public function sendCaptureRequest();

    public function setCaptureResponse($gatewayResponse);

    public function getCaptureResponse();

    public function setIsCaptured($isCaptured);

    public function getIsCaptured();

    public function authorizeAndCapture();

    /**
     * Capture a payment
     *  This does not handle pre-authorized payment transactions
     *
     * @return mixed
     */
    public function purchase();

    public function buildPurchaseRequest();

    public function setPurchaseRequest($gatewayRequest);

    public function getPurchaseRequest();

    public function sendPurchaseRequest();

    public function setPurchaseResponse($gatewayResponse);

    public function getPurchaseResponse();

    public function setIsPurchased($isPurchased);

    public function getIsPurchased();


}
