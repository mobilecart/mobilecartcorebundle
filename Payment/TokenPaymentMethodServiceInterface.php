<?php

namespace MobileCart\CoreBundle\Payment;

interface TokenPaymentMethodServiceInterface extends PaymentMethodServiceInterface
{
    /**
     * All tokens for the relevant customer
     *
     * @param array $customerTokens
     * @return mixed
     */
    public function setCustomerTokens(array $customerTokens);

    /**
     * @return mixed
     */
    public function getCustomerTokens();

    /**
     * @param $token
     * @return mixed
     */
    public function setPaymentCustomerToken($token);

    /**
     * @return mixed
     */
    public function getPaymentCustomerToken();

    /**
     * @param $subscriptionCustomer
     * @return mixed
     */
    public function setSubscriptionCustomer($subscriptionCustomer);

    /**
     * @return mixed
     */
    public function getSubscriptionCustomer();

    /**
     * Create a Token for use in multiple payments
     *
     * @return mixed
     */
    public function createToken();

    public function buildTokenCreateRequest();

    public function setTokenCreateRequest($tokenCreateRequest);

    public function getTokenCreateRequest();

    public function sendTokenCreateRequest();

    public function setTokenCreateResponse($tokenCreateResponse);

    public function getTokenCreateResponse();

    public function setIsTokenCreated($isTokenCreated);

    public function getIsTokenCreated();

    /**
     * Extract/Build array of data, ie from tokenCreateResponse, paymentData, and orderData
     *  for populating a CustomerToken entity
     *
     * @return mixed
     */
    public function extractCustomerTokenData();

    /**
     * Capture a payment using a stored Token
     *
     * @return mixed
     */
    public function purchaseStoredToken();

    public function buildTokenPaymentRequest();

    public function setTokenPaymentRequest($tokenPaymentRequest);

    public function getTokenPaymentRequest();

    public function sendTokenPaymentRequest();

    public function setTokenPaymentResponse($tokenPaymentResponse);

    public function getTokenPaymentResponse();

    public function setIsPurchasedStoredToken($isPurchasedStoredToken);

    public function getIsPurchasedStoredToken();

    /**
     * Capture a payment and subscribe to the processor's
     *  automatic recurring billing service
     *
     * @return mixed
     */
    public function purchaseAndSubscribeRecurring();

    public function buildSubscribeRecurringRequest();

    public function setSubscribeRecurringRequest($subscribeRecurringRequest);

    public function getSubscribeRecurringRequest();

    public function sendSubscribeRecurringRequest();

    public function setSubscribeRecurringResponse($subscribeRecurringResponse);

    public function getSubscribeRecurringResponse();

    public function setIsPurchasedAndSubscribedRecurring($isPurchasedAndSubscribedRecurring);

    public function getIsPurchasedAndSubscribedRecurring();

    /**
     * Cancel a recurring subscription
     *  which is handled by a 3rd party service
     *
     * @return mixed
     */
    public function cancelRecurring();

    public function buildCancelRecurringRequest();

    public function setCancelRecurringRequest($cancelRecurringRequest);

    public function getCancelRecurringRequest();

    public function sendCancelRecurringRequest();

    public function setCancelRecurringResponse($cancelRecurringResponse);

    public function getCancelRecurringResponse();

    public function setIsCanceledRecurring($isCanceled);

    public function getIsCanceledRecurring();
}
