<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class RemoveProduct
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class RemoveProduct extends BaseCartListener
{
    /**
     * @param CoreEvent $event
     */
    public function onCartRemoveProduct(CoreEvent $event)
    {
        // parse/convert API requests
        switch($event->getContentType()) {
            case 'application/json':

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array)json_decode($event->getRequest()->getContent());

                if (isset($apiRequest['sku']) || isset($apiRequest['product_id'])) {
                    $keys = ['product_id', 'sku'];
                    foreach ($apiRequest as $key => $value) {
                        if (!in_array($key, $keys)) {
                            continue;
                        }

                        $event->getRequest()->request->set($key, $value);
                    }
                }
                break;
            default:

                break;
        }

        // start base logic
        $recollectShipping = $event->get('recollect_shipping', []);
        $success = false;
        $request = $event->getRequest();
        $this->initCart($request);

        $key = $request->get('sku') || $event->get('sku')
            ? 'sku'
            : 'product_id';

        $value = $event->get($key)
            ? $event->get($key)
            : $request->get($key, '');

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

            $success = true;
        } else {
            $event->addErrorMessage("Specified item is not in your cart");
        }

        $event->setSuccess($success);
    }
}
