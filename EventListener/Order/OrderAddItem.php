<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Shipping\RateRequest;

/**
 * Class OrderAddItem
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderAddItem
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
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
    public function onOrderAddItem(CoreEvent $event)
    {
        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $this->getCartService()->initCartJson($cartJson);
        $itemJson = $request->get('item', '{}');

        $item = $this->getCartService()->getItemInstance();
        $item->fromJson($itemJson);
        $productId = $item->getProductId();
        $qty = $item->getQty();

        if ($this->getCartService()->hasProductId($productId)) {
            $this->getCartService()->setProductQty($productId, $qty);
        } elseif ($productId) {
            $this->getCartService()->addItem($item);
        }

        $rateRequest = new RateRequest();

        $cart = $this->getCartService()
            ->collectShippingMethods($rateRequest)
            ->collectTotals()
            ->getCart();

        // todo: implement getCartDiscounts()

        $discounts = $this->getDiscountService()
            ->setCart($cart)
            ->getAutoDiscounts(true);

        $event->setReturnData('cart', $cart);
        $event->setReturnData('discounts', $discounts);
    }
}
