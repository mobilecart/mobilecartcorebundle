<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderAddItem
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderAddItem
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
     * @param CoreEvent $event
     */
    public function onOrderAddItem(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        // set shipment method on cart
        $cartJson = $request->get('cart', '{}');
        $this->getCartSession()->initCartJson($cartJson);
        $itemJson = $request->get('item', '{}');

        $item = $this->getCartSession()->getItemInstance();
        $item->fromJson($itemJson);
        $productId = $item->getProductId();
        $qty = $item->getQty();

        if ($this->getCartSession()->hasProductId($productId)) {

            $this->getCartSession()
                ->setProductQty($productId, $qty);

        } elseif ($productId) {
            $this->getCartSession()->addItem($item);
        }

        $cart = $this->getCartSession()
            ->collectShippingMethods()
            ->collectTotals()
            ->getCart();

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
