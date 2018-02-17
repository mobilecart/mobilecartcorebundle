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
     * @param CoreEvent $event
     */
    public function onCartRemoveProduct(CoreEvent $event)
    {
        $isValid = false;

        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array)json_decode($event->getRequest()->getContent());

                $key = isset($apiRequest['sku']) || $event->get('sku')
                    ? 'sku'
                    : 'product_id';

                $value = isset($apiRequest[$key])
                    ? $apiRequest[$key]
                    : '';

                break;
            default:

                $key = $event->getRequest()->get('sku') || $event->get('sku')
                    ? 'sku'
                    : 'product_id';

                $value = $event->get($key)
                    ? $event->get($key)
                    : $event->getRequest()->get($key, '');

                break;
        }

        // start base logic
        $recollectShipping = $event->get('recollect_shipping', []);
        $cartItem = $this->getCartService()->getCart()->findItem($key, $value);
        if ($cartItem) {

            $customerAddressId = $cartItem->get('customer_address_id', 'main');
            $srcAddressKey = $cartItem->get('source_address_key', 'main');

            $this->getCartService()->removeItem($key, $value);
            $this->getCartService()->deleteItemEntity($key, $value);

            if ($this->getCartService()->getCart()->hasItems()) {

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

            $isValid = true;
        } else {
            $event->addErrorMessage("Specified item is not in your cart");
            $event->setResponseCode(400);
        }

        $event->setSuccess($isValid);
        if ($event->getSuccess()) {
            $event->addSuccessMessage('Cart Updated !');
        }
    }
}
