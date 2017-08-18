<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\CartComponent\Cart;

/**
 * Class OrderShipmentInsert
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
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
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
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
     * @param CoreEvent $event
     */
    public function onOrderShipmentInsert(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
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
            ->submitCart()
            ->getOrder();

        $event->setOrder($order);

        if ($order && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Order Created!'
            );
        }

        $event->setReturnData($returnData);
    }
}
