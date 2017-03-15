<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

/**
 * Class CheckoutSessionService
 * @package MobileCart\CoreBundle\Service
 *
 * This class handles configuration and session variables
 *  for the Checkout
 */
class CheckoutSessionService
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @var float
     */
    protected $minimumOrderGrandTotal = 1.0;

    /**
     * @var string
     */
    protected $paymentMethodCode = '';

    /**
     * @var array
     */
    protected $paymentData = [];

    /**
     * @param bool $yesNo
     * @return $this
     */
    public function setIsValidBillingAddress($yesNo)
    {
        $this->getCartSessionService()->getSession()->set('is_valid_billing_address', $yesNo);
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsValidBillingAddress()
    {
        return $this->getCartSessionService()->getSession()->get('is_valid_billing_address', false);
    }

    /**
     * @param bool $yesNo
     * @return $this
     */
    public function setIsValidShippingAddress($yesNo)
    {
        $this->getCartSessionService()->getSession()->set('is_valid_shipping_address', $yesNo);
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsValidShippingAddress()
    {
        return $this->getCartSessionService()->getSession()->get('is_valid_shipping_address', false);
    }

    /**
     * @param bool $yesNo
     * @return $this
     */
    public function setIsValidShippingMethod($yesNo)
    {
        $this->getCartSessionService()->getSession()->set('is_valid_shipping_method', $yesNo);
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsValidShippingMethod()
    {
        return $this->getCartSessionService()->getSession()->get('is_valid_shipping_method', false);
    }

    /**
     * @param bool $yesNo
     * @return $this
     */
    public function setIsValidTotals($yesNo)
    {
        $this->getCartSessionService()->getSession()->set('is_valid_totals', $yesNo);
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsValidTotals()
    {
        return $this->getCartSessionService()->getSession()->get('is_valid_totals', false);
    }

    /**
     * @param bool $yesNo
     * @return $this
     */
    public function setIsValidPaymentMethod($yesNo)
    {
        $this->getCartSessionService()->getSession()->set('is_valid_payment_method', $yesNo);
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsValidPaymentMethod()
    {
        return $this->getCartSessionService()->getSession()->get('is_valid_payment_method', false);
    }

    /**
     * @param array $paymentData
     * @return $this
     */
    public function setPaymentData(array $paymentData)
    {
        $this->getCartSessionService()->getSession()->set('payment_data', $paymentData);
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        return $this->getCartSessionService()->getSession()->get('payment_data', []);
    }

    /**
     * @param $paymentMethodCode
     * @return $this
     */
    public function setPaymentMethodCode($paymentMethodCode)
    {
        $this->getCartSessionService()->getSession()->set('payment_method_code', $paymentMethodCode);
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentMethodCode()
    {
        return $this->getCartSessionService()->getSession()->get('payment_method_code', '');
    }

    /**
     * @param \MobileCart\CoreBundle\Service\CartSessionService $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;

        if (!$cartSessionService->getCartService()->getShippingService()->getIsShippingEnabled()) {

            $this->setIsValidShippingAddress(1)
                ->setIsValidShippingMethod(1);
        }

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
     * @param \MobileCart\CoreBundle\Service\OrderService $orderService
     * @return $this
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return bool
     */
    public function getAllowGuestCheckout()
    {
        return $this->getCartSessionService()->getCartService()->getAllowGuestCheckout();
    }

    /**
     * @param float $total
     * @return $this
     */
    public function setMinimumOrderGrandTotal($total)
    {
        $this->minimumOrderGrandTotal = $total;
        return $this;
    }

    /**
     * @return string
     */
    public function getMinimumOrderGrandTotal()
    {
        return $this->minimumOrderGrandTotal;
    }

    /**
     * @return array
     */
    public function getAllowedCountryIds()
    {
        return $this->getCartSessionService()->getAllowedCountryIds();
    }

    /**
     * @param $code
     * @return bool|PaymentMethodServiceInterface
     */
    public function findPaymentMethodServiceByCode($code)
    {
        return $this->getOrderService()
            ->getPaymentService()
            ->findPaymentMethodServiceByCode($code);
    }
}
