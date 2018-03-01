<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Customer;

/**
 * Class OrderUpdateCustomer
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderUpdateCustomer
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
    public function onOrderUpdateCustomer(CoreEvent $event)
    {
        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $customerJson = $request->get('customer', '{}');

        // update cart customer
        $customer = new Customer();
        $customer->fromJson($customerJson);

        $cart = $this->getCartService()
            ->initCartJson($cartJson)
            ->setCustomer($customer)
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
