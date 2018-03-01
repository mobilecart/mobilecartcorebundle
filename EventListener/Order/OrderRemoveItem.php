<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderRemoveItem
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderRemoveItem
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
     * @return \MobileCart\CoreBundle\Service\DiscountService
     */
    public function getDiscountService()
    {
        return $this->getCartService()->getDiscountService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderRemoveItem(CoreEvent $event)
    {
        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $productId = $request->get('product_id', '');

        $cart = $this->getCartService()
            ->initCartJson($cartJson)
            ->removeProductId($productId)
            ->collectTotals()
            ->getCart();

        // todo: implement getCartDiscounts()

        $discounts = $this->getDiscountService()
            ->setCart($cart)
            ->getAutoDiscounts(true);

        $event->setReturnData('discounts', $discounts);
        $event->setReturnData('cart', $cart);
    }
}
