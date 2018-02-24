<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutSubmitOrder
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutSubmitOrder
{
    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

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
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\PaymentService
     */
    public function getPaymentService()
    {
        return $this->getOrderService()->getPaymentService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getOrderService()->getEntityService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutSubmitOrder(CoreEvent $event)
    {
        // todo : keep a count of invalid requests, logout/lockout user if excessive

        $isValid = false;
        $invalidSections = $this->getCartService()->getInvalidSections();

        // cannot submit order if there are invalid sections
        if ($invalidSections) {

            $event->setSuccess($isValid);
            $event->setReturnData('invalid_sections', $invalidSections);
            // response is rendered in CheckoutSubmitOrderReturn
            return false; // return early, return value has no effect
        }

        // locate the payment service, using the payment method code stored on the cart entity
        $paymentMethodService = $this->getPaymentService()
            ->findPaymentMethodServiceByCode($this->getCartService()->getPaymentMethodCode());

        if (!$paymentMethodService) {
            throw new \Exception("Cannot find payment service: {$this->getCartService()->getPaymentMethodCode()}");
        }

        // get the payment data stored on the cart entity
        $paymentData = $this->getCartService()->getPaymentData();

        // merge data
        $returnPaymentData = $event->getReturnData('payment_data', []);
        if ($returnPaymentData) {
            foreach($returnPaymentData as $k => $v) {
                $paymentData[$k] = $v;
            }
        }

        $orderService = $this->getOrderService()
            ->setCart($this->getCartService()->getCart())
            ->setPaymentMethodService($paymentMethodService)
            ->setPaymentData($paymentData)
            ->setUser($event->getUser());

        // create order, orderItems, orderShipments, orderInvoice
        //  and payment, if necessary

        $orderService->submitCart();

        if ($orderService->getSuccess()) {

            $isValid = true;
            $event->setSuccess($isValid);

            $event->set('cart', $this->getOrderService()->getCart())
                ->set('order', $this->getOrderService()->getOrder())
                ->set('customer_token', $this->getOrderService()->getCustomerToken())
                ->set('payment', $this->getOrderService()->getPayment())
                ->set('invoice', $this->getOrderService()->getInvoice());

            $event->setReturnData('order_id', $this->getOrderService()->getOrder()->getId());

            // further processing happens in the next event listeners

        } else {

            $event->setSuccess($isValid);

            if ($orderService->getErrors()) {
                foreach ($orderService->getErrors() as $error) {
                    $event->addErrorMessage($error);
                }
            }

            $event->setReturnData('invalid_sections', $invalidSections);
        }
    }
}
