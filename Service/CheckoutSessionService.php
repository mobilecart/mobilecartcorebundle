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

use MobileCart\CoreBundle\Constants\CheckoutConstants;

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
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutFormService
     */
    protected $checkoutFormService;

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
     * @return $this
     */
    public function initSections()
    {
        $sections = $this->getCartService()->getSession()->get('checkout_sections', []);
        if (!$sections) {
            $sections = [];
            $sectionKeys = $this->getCheckoutFormService()->getSectionKeys();
            if ($sectionKeys) {
                foreach($sectionKeys as $sectionKey) {
                    $sections[$sectionKey] = false;
                }
            }
            $this->getCartService()->getSession()->set('checkout_sections', $sections);
        }

        return $this;
    }

    /**
     * @param string $section
     * @param bool $isValid
     * @return $this
     */
    public function setSectionIsValid($section, $isValid = true)
    {
        $this->initSections();
        $sections = $this->getCartService()->getSession()->get('checkout_sections', []);
        $sections[$section] = $isValid;
        $this->getCartService()->getSession()->set('checkout_sections', $sections);
        return $this;
    }

    /**
     * @param $section
     * @return bool
     */
    public function getSectionIsValid($section)
    {
        $this->initSections();
        $sections = $this->getCartService()->getSession()->get('checkout_sections', []);
        return (bool) isset($sections[$section])
            ? $sections[$section]
            : false;
    }

    /**
     * @return array
     */
    public function getInvalidSections()
    {
        $invalid = [];
        $this->initSections();
        $sections = $this->getCartService()->getSession()->get('checkout_sections', []);
        if ($sections) {
            foreach($sections as $section => $isValid) {
                if (!$isValid) {
                    $invalid[] = $section;
                }
            }
        }

        return $invalid;
    }

    /**
     * @return bool
     */
    public function getIsAllValid()
    {
        $this->initSections();
        $sections = $this->getCartService()->getSession()->get('checkout_sections', []);
        if ($sections) {
            foreach($sections as $section => $isValid) {
                if (!$isValid) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array $paymentData
     * @return $this
     */
    public function setPaymentData(array $paymentData)
    {
        $this->getCartService()->getSession()->set('payment_data', $paymentData);
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        return $this->getCartService()->getSession()->get('payment_data', []);
    }

    /**
     * @param $paymentMethodCode
     * @return $this
     */
    public function setPaymentMethodCode($paymentMethodCode)
    {
        $this->getCartService()->getSession()->set('payment_method_code', $paymentMethodCode);
        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentMethodCode()
    {
        return $this->getCartService()->getSession()->get('payment_method_code', '');
    }

    /**
     * @return CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
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
     * @param CheckoutFormService $checkoutFormService
     * @return $this
     */
    public function setCheckoutFormService(\MobileCart\CoreBundle\Service\CheckoutFormService $checkoutFormService)
    {
        $this->checkoutFormService = $checkoutFormService;
        return $this;
    }

    /**
     * @return CheckoutFormService
     */
    public function getCheckoutFormService()
    {
        return $this->checkoutFormService;
    }

    /**
     * @return bool
     */
    public function getAllowGuestCheckout()
    {
        return $this->getCartService()->getAllowGuestCheckout();
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
        return $this->getCartService()->getAllowedCountryIds();
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
