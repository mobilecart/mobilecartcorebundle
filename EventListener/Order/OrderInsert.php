<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Cart;

/**
 * Class OrderInsert
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderInsert
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
    public function onOrderInsert(CoreEvent $event)
    {
        $formData = $event->getFormData();
        $request = $event->getRequest();

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

        $cartTotalService = $this->getOrderService()->getCartTotalService();
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

        if ($event->getEntity()->getCustomer()) {
            $event->getEntity()->setEmail($event->getEntity()->getCustomer()->getEmail());
        }

        $this->getOrderService()
            ->setCart($cart)
            ->setOrder($event->getEntity())
            ->setRequest($event->getRequest())
            ->setFormData($event->getFormData())
            ->setEnableCreatePayment($createPayment)
            ->setIsRefund(false)
            ->setPaymentMethodCode($paymentMethod)
            ->setOrderPaymentData($paymentInfo)
            ->setUser($event->getUser())
            ->submitCart();

        $event->addSuccessMessage('Order Created!');
    }
}
