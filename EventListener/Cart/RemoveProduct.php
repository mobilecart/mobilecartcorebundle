<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class RemoveProduct
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class RemoveProduct
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

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
     * @param CoreEvent $event
     */
    public function onCartRemoveProduct(CoreEvent $event)
    {
        $recollectShipping = $event->get('recollect_shipping', []);
        $success = false;
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $event->set('format', $format);

        $productId = $event->getProductId()
            ? $event->getProductId()
            : $request->get('product_id', '');

        $cart = $this->getCartService()->getCart();

        $cartItem = $cart->findItem('product_id', $productId);
        if ($cartItem) {

            $customerAddressId = $cartItem->get('customer_address_id', 'main');
            $srcAddressKey = $cartItem->get('source_address_key', 'main');

            $this->getCartService()->removeProductId($productId);
            $this->getCartService()->deleteItemEntity('product_id', $productId);

            if ($cart->getItems()) {

                $recollectShipping[] = new ArrayWrapper([
                    'customer_address_id' => $customerAddressId,
                    'source_address_key' => $srcAddressKey
                ]);

                $event->setRecollectShipping($recollectShipping);
            } else {
                // remove all shipments and methods if the cart is empty
                $this->getCartService()->removeShipments();
                $this->getCartService()->removeShippingMethods();
            }

            $success = true;
        } else {
            $event->addErrorMessage("Specified item is not in your cart");
        }

        $event->setReturnData('cart', $cart);
        $event->setReturnData('success', $success);
    }
}
