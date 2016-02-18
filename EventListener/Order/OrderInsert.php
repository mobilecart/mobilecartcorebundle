<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Payment\ServiceRequest;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Cart;

class OrderInsert
{
    protected $entityService;

    protected $currencyService;

    protected $orderService;

    protected $cartSessionService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    public function getOrderService()
    {
        return $this->orderService;
    }

    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    public function onOrderInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        $customerId = isset($formData['customer'])
            ? $formData['customer']
            : 0;

        $customer = $this->getEntityService()
            ->find(EntityConstants::CUSTOMER, $customerId);

        $customerName = $customer
            ? $customer->getFirstName() . ' ' . $customer->getLastName()
            : '';

        $isRefund = false;

        $cart = new Cart();
        switch($event->getSection()) {
            case CoreEvent::SECTION_BACKEND:
                $cartJson = $formData['json'];
                $cart->importJson($cartJson);
                break;
            case CoreEvent::SECTION_FRONTEND:
                $cart = $this->getCartSessionService()->getCart();
                break;
            case CoreEvent::SECTION_API:

                break;
            default:

                break;
        }

        $cartTotalService = $this->getOrderService()
            ->getCartTotalService();

        $cartTotalService->setCart($cart);

        $totals = $cartTotalService
            ->collectTotals()
            ->getTotals();

        $cart->setTotals($totals);

        // this is passed outside of the order form
        $paymentMethod = $request->get('payment_method', '');

        $paymentInfo = $paymentMethod
            ? $request->request->get($paymentMethod)
            : [];

        $createPayment = ($paymentMethod && $paymentInfo);

        $order = $this->getOrderService()
            ->setCart($cart)
            ->setOrder($entity)
            ->setRequest($event->getRequest())
            ->setFormData($event->getFormData())
            ->setCreatePayment($createPayment)
            ->setIsRefund($isRefund)
            ->setPaymentMethod($paymentMethod)
            ->setPaymentFormData($paymentInfo)
            // ->setShipmentMethod()
            // ->setShipmentData()
            ->submitCart()
            ->getOrder();

        $event->setOrder($order);

        // todo: setOrderItems(), setInvoice(), etc

        $event->setReturnData($returnData);
    }
}
