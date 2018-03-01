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
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCurrencyService();
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
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderInsert(CoreEvent $event)
    {
        $formData = $event->getFormData();
        $request = $event->getRequest();

        $this->getCartService()->initCartJson($formData['json']);
        $this->getCartService()->collectTotals();

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
            ->setCart($this->getCartService()->getCart())
            ->setOrder($event->getEntity())
            ->setRequest($event->getRequest())
            ->setFormData($event->getFormData())
            ->setEnableCreatePayment($createPayment)
            ->setIsRefund(false)
            ->setPaymentMethodCode($paymentMethod)
            ->setOrderPaymentData($paymentInfo)
            ->setUser($event->getUser())
            ->submitCart();

        if ($this->getOrderService()->getSuccess()) {
            $event->setSuccess(true);
            $event->addSuccessMessage('Order Created !');
        } else {
            $event->addErrorMessage('An error occurred while saving Order');
            if ($this->getOrderService()->getErrors()) {
                foreach($this->getOrderService()->getErrors() as $error) {
                    $event->addErrorMessage($error);
                }
            }
        }
    }
}
