<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Item;

/**
 * Class OrderRemoveItem
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderRemoveItem
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSession;

    /**
     * @var \MobileCart\CoreBundle\Service\CartTotalService
     */
    protected $cartTotalService;

    /**
     * @var \MobileCart\CoreBundle\Service\DiscountService
     */
    protected $discountService;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

    /**
     * @param $cartSession
     * @return $this
     */
    public function setCartSession($cartSession)
    {
        $this->cartSession = $cartSession;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSession()
    {
        return $this->cartSession;
    }

    /**
     * @param $cartTotalService
     * @return $this
     */
    public function setCartTotalService($cartTotalService)
    {
        $this->cartTotalService = $cartTotalService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
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
     * @return \MobileCart\CoreBundle\Service\DiscountService
     */
    public function getDiscountService()
    {
        return $this->discountService;
    }

    /**
     * @param Event $event
     */
    public function onOrderRemoveItem(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $productId = $request->get('product_id', '');

        $cart = $this->getCartSession()
            ->initCartJson($cartJson)
            ->getCart();

        $cart->removeProductId($productId);

        $totals = $this->getCartTotalService()
            ->setCart($cart)
            //->setApplyAutoDiscounts(1)
            ->collectTotals()
            ->getTotals();

        $cart->setTotals($totals);

        $returnData['cart'] = $cart;

        $excludeDiscountIds = [];

        // todo: implement getCartDiscounts()

        $discounts = $this->getDiscountService()
            ->setCart($cart)
            ->getAutoDiscounts(true);

        $returnData['discounts'] = $discounts;

        $event->setReturnData($returnData);
    }
}
