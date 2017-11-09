<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Discount;

/**
 * Class OrderAddDiscount
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderAddDiscount
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->getCartService()->getCartTotalService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DiscountService
     */
    public function getDiscountService()
    {
        return $this->getCartService()->getDiscountService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderAddDiscount(CoreEvent $event)
    {
        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $discountId = $request->get('discount_id', '');

        $cart = $this->getCartService()
            ->initCartJson($cartJson)
            ->getCart();

        // load discount
        $entity = $this->getDiscountService()->find($discountId);

        // convert discount
        $discount = new Discount();
        $discount->fromArray($entity->getBaseData());
        //$discount->setAppliedAs($entity->getAppliedAs());
        //$discount->setAppliedTo($entity->getAppliedTo());

        // todo : set product ids or shipping method

        // add discount
        $cart->addDiscount($discount);

        $totals = $this->getCartTotalService()
            ->setCart($cart)
            //->setApplyAutoDiscounts(1)
            ->collectTotals()
            ->getTotals();

        $cart->setTotals($totals);

        // todo: implement getCartDiscounts()

        $discounts = $this->getDiscountService()
            ->setCart($cart)
            ->getAutoDiscounts(true);

        $event->setReturnData('discounts', $discounts);
        $event->setReturnData('cart', $cart);
    }
}
