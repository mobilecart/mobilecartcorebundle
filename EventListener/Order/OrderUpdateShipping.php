<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\EventDispatcher\Event;

class OrderUpdateShipping
{

    protected $cartSession;

    protected $cartTotalService;

    protected $discountService;

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

    public function setCartSession($cartSession)
    {
        $this->cartSession = $cartSession;
        return $this;
    }

    public function getCartSession()
    {
        return $this->cartSession;
    }

    public function setCartTotalService($cartTotalService)
    {
        $this->cartTotalService = $cartTotalService;
        return $this;
    }

    public function getCartTotalService()
    {
        return $this->cartTotalService;
    }

    /**
     * @param $discountService
     * @return $this
     */
    public function setDiscountService($discountService)
    {
        $this->discountService = $discountService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountService()
    {
        return $this->discountService;
    }

    public function onOrderUpdateShipping(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        /*
        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $methodJson = $request->get('shipping_method', '{}');

        $method = json_decode($methodJson);
        $shippingMethodCode = $method->code;

        $cart = $this->getCartSession()
            ->initCartJson($cartJson)
            ->getCart();

        if ($cart->hasShippingMethodCode($shippingMethodCode)) {
            $shippingMethod = $cart->findShippingMethod('code', $shippingMethodCode);
            if ($shippingMethod) {
                $cart->unsetShipments();
                $cart->addShipment($shippingMethod);
            }
        }

        $totals = $this->getCartTotalService()
            ->setCart($cart)
            //->setApplyAutoDiscounts(1)
            ->collectTotals()
            ->getTotals();

        //$returnData['totals'] = $totals;

        $cart->setTotals($totals);

        $returnData['cart'] = $cart; //->toArray();

        $excludeDiscountIds = [];

        // todo: implement getCartDiscounts()

        $discounts = $this->getDiscountService()
            ->setCart($cart)
            ->getAutoDiscounts(true);

        $returnData['discounts'] = $discounts;

        $event->setReturnData($returnData); //*/
    }
}
