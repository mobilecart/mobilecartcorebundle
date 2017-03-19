<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Discount;

/**
 * Class OrderAddDiscount
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderAddDiscount
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
    public function onOrderAddDiscount(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $discountId = $request->get('discount_id', '');

        $cart = $this->getCartSession()
            ->initCartJson($cartJson)
            ->getCart();

        // load discount
        $entity = $this->getDiscountService()
            ->find($discountId);

        // convert discount
        $discount = new Discount();
        $discount->fromArray($entity->getBaseData());
        $discount->set('as', $discount->getAppliedAs());
        $discount->set('to', $discount->getAppliedTo());

        // todo : set product ids or shipping method

        // add discount
        $cart->addDiscount($discount);

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
