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

        $discountId = $request->get('discount_id', '');

        $cartJson = $request->get('cart', '{}');
        $this->getCartService()->initCartJson($cartJson);

        // load discount
        $entity = $this->getDiscountService()->find($discountId);
        if (!$entity) {
            $event->setSuccess(false);
            $event->addErrorMessage('Discount not found');
            $event->setReturnData('cart', $this->getCartService()->getCart());
            return;
        }

        // add discount
        $this->getCartService()->reapplyDiscountEntityIfValid($entity);
        $this->getCartService()->collectTotals();
        $event->setReturnData('cart', $this->getCartService()->getCart());
    }
}
