<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutSubmitOrder
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutSubmitOrder
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    protected $router;

    /**
     * @param $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param $checkoutSessionService
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    /**
     * @param $orderService
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
     * @param $router
     * @return $this
     */
    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function onCheckoutSubmitOrder(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $isValid = 0;
        $returnData['messages'] = []; // r[messages] = [msg, msg, msg]
        $returnData['invalid_sections'] = []; // r[invalid_sections] = [a, b, c]
        $returnData['invalid'] = []; // r[invalid][section][field] = [msg, msg, msg]
        $returnData['success'] = $isValid;

        $shippingService = $this->getOrderService()
            ->getCartService()
            ->getShippingService();

        // todo : keep a count of invalid requests, logout/lockout user if excessive
        // todo : change this to be more abstract , possibly use an event with listeners

        if (!$this->getCheckoutSessionService()->getIsValidBillingAddress()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_BILLING_ADDRESS;
        }

        if ($shippingService->getIsShippingEnabled() && !$this->getCheckoutSessionService()->getIsValidShippingAddress()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_SHIPPING_ADDRESS;
        }

        if (!$this->getCheckoutSessionService()->getIsValidTotals()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_TOTALS_DISCOUNTS;
        }

        if (!$this->getCheckoutSessionService()->getIsValidPaymentMethod()) {
            $returnData['invalid_sections'][] = CheckoutConstants::STEP_PAYMENT_METHODS;
        }

        if ($returnData['invalid_sections']) {
            $returnData['success'] = 0;

            if (isset($returnData['form'])) {
                unset($returnData['form']); // fix warning: Recursion Detected
            }

            if (isset($returnData['sections'])) {
                unset($returnData['sections']);
            }

            $response = new JsonResponse($returnData);

            $event->setReturnData($returnData)
                ->setResponse($response);

            return false; // return early, return value has no effect
        }

        $paymentMethodCode = $this->getCheckoutSessionService()
            ->getPaymentMethodCode();

        $paymentMethodService = $this->getCheckoutSessionService()
            ->findPaymentMethodServiceByCode($paymentMethodCode);

        $paymentData = $this->getCheckoutSessionService()
            ->getPaymentData();

        if (isset($returnData['payment_data']) && is_array($returnData['payment_data'])) {
            foreach($returnData['payment_data'] as $k => $v) {
                $paymentData[$k] = $v;
            }
        }

        $cart = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCart();

        $orderService = $this->getOrderService()
            ->setCart($cart)
            ->setPaymentMethodService($paymentMethodService)
            ->setPaymentData($paymentData);

        // create order, orderItems, orderShipments, orderInvoice
        //  and payment, if necessary

        try {
            $orderService->submitCart();
            $isValid = 1;
        } catch(\Exception $e) {
            $returnData['messages'] = $e->getMessage(); // todo : build on this, handle all exceptions
        }

        $event->setIsValid($isValid);

        if ($isValid) {

            $event->setOrder($orderService->getOrder());

            $this->getCheckoutSessionService()
                ->getCartSessionService()
                ->removeItems()
                ->getSession()
                ->set('order_id', $orderService->getOrder()->getId());

            $returnData['redirect_url'] = $this->getRouter()->generate('cart_checkout_success', []);
        } else {

            if (!$this->getCheckoutSessionService()->getIsValidBillingAddress()) {
                $returnData['invalid_sections'][] = CheckoutConstants::STEP_BILLING_ADDRESS;
            }

            if ($shippingService->getIsShippingEnabled() && !$this->getCheckoutSessionService()->getIsValidShippingAddress()) {
                $returnData['invalid_sections'][] = CheckoutConstants::STEP_SHIPPING_ADDRESS;
            }

            if ($shippingService->getIsShippingEnabled() && !$this->getCheckoutSessionService()->getIsValidShippingMethod()) {
                $returnData['invalid_sections'][] = CheckoutConstants::STEP_SHIPPING_METHOD;
            }

            if (!$this->getCheckoutSessionService()->getIsValidTotals()) {
                $returnData['invalid_sections'][] = CheckoutConstants::STEP_TOTALS_DISCOUNTS;
            }

            if (!$this->getCheckoutSessionService()->getIsValidPaymentMethod()) {
                $returnData['invalid_sections'][] = CheckoutConstants::STEP_PAYMENT_METHODS;
            }
        }

        $returnData['success'] = $isValid;
        if (isset($returnData['form'])) {
            unset($returnData['form']); // fix warning: Recursion Detected
        }

        if (isset($returnData['sections'])) {
            unset($returnData['sections']);
        }

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
