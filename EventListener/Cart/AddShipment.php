<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\Shipment;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants as EC;

/**
 * Class AddShipment
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class AddShipment
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
     * @return \MobileCart\CoreBundle\Service\ShippingService
     */
    public function getShippingService()
    {
        return $this->getCartService()->getShippingService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartAddShipment(CoreEvent $event)
    {
        $isValid = false;
        $shippingMethods = []; // r[source_address_key][customer_address_id] = $code
        $shippingMethod = ''; // single shipping method code

        // parse/convert API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                $keys = [
                    EC::SOURCE_ADDRESS_KEY,
                    EC::CUSTOMER_ADDRESS_ID,
                    EC::SHIPPING_METHOD
                ];

                if (isset($apiRequest['shipping_methods'])) {
                    foreach($apiRequest as $data) {

                        $isValid = true;
                        $data = is_object($data) ? get_object_vars($data) : $data;
                        foreach($keys as $key) {
                            if (!isset($data[$key])) {
                                $isValid = false;
                            }
                        }

                        if (!$isValid) {
                            continue;
                        }

                        $sourceAddressKey = $data[EC::SOURCE_ADDRESS_KEY];
                        $customerAddressId = $data[EC::CUSTOMER_ADDRESS_ID];
                        $shippingMethod = $data[EC::SHIPPING_METHOD];

                        if (!isset($shippingMethods[$sourceAddressKey])) {
                            $shippingMethods[$sourceAddressKey] = [];
                        }

                        $shippingMethods[$sourceAddressKey][$customerAddressId] = $shippingMethod;
                    }
                } elseif (isset($apiRequest[EC::SHIPPING_METHOD])) {

                    $shippingMethod = $apiRequest[EC::SHIPPING_METHOD];

                    if ($this->getShippingService()->getIsMultiShippingEnabled()) {

                        $sourceAddressKey = isset($apiRequest[EC::SOURCE_ADDRESS_KEY])
                            ? $apiRequest[EC::SOURCE_ADDRESS_KEY]
                            : 'main';

                        $customerAddressId = isset($apiRequest[EC::CUSTOMER_ADDRESS_ID])
                            ? $apiRequest[EC::CUSTOMER_ADDRESS_ID]
                            : 'main';

                        if (!isset($shippingMethods[$sourceAddressKey])) {
                            $shippingMethods[$sourceAddressKey] = [];
                        }

                        $shippingMethods[$sourceAddressKey][$customerAddressId] = $shippingMethod;
                    }
                }

                break;
            default:

                $shippingMethods = $event->getRequest()->get('shipping_methods', []); // r[source_address_key][customer_address_id] = $code
                $shippingMethod = $event->getRequest()->get(EC::SHIPPING_METHOD, ''); // single shipping method

                break;
        }

        // handle multiple shipments, if necessary
        if ($this->getShippingService()->getIsMultiShippingEnabled()) {

            if (is_array($shippingMethods) && count($shippingMethods)) {
                foreach($shippingMethods as $srcAddressKey => $customerAddressIds) {

                    if (!$customerAddressIds) {
                        continue;
                    }

                    foreach($customerAddressIds as $anAddressId => $methodCode) {
                        $anAddressId = $this->getCartService()->unprefixAddressId($anAddressId);

                        if ($rate = $this->getCartService()->getCart()->findShippingMethod('code', $methodCode, $anAddressId, $srcAddressKey)) {
                            $productIds = [];
                            if ($this->getCartService()->hasItems()) {
                                foreach($this->getCartService()->getItems() as $item) {
                                    if ($item->get(EC::CUSTOMER_ADDRESS_ID, 'main') == $anAddressId
                                        && $item->get(EC::SOURCE_ADDRESS_KEY, 'main') == $srcAddressKey
                                    ) {
                                        $productIds[] = $item->getProductId();
                                    }
                                }
                            }

                            $shipment = new Shipment();
                            $shipment->fromArray($rate->getData());

                            $this->getCartService()->removeShipments($anAddressId, $srcAddressKey);
                            $this->getCartService()->addShipment($shipment);

                            $isValid = true;
                            break;
                        }
                    }
                }
            }
        } else {
            // otherwise, assume a single shipment and shipping method

            if ($this->getCartService()->hasShippingMethodCode($shippingMethod)) {

                $rate = $this->getCartService()->getCart()->findShippingMethod('code', $shippingMethod);

                $shipment = new Shipment();
                $shipment->fromArray($rate->getData());

                $this->getCartService()->removeShipments();
                $this->getCartService()->addShipment($shipment);

                $isValid = true;
            }
        }

        $event->setSuccess($isValid);
    }
}
