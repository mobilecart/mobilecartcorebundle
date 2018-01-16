<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\Shipment;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants as EC;

/**
 * Class AddShipment
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class AddShipment extends BaseCartListener
{
    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    public $shippingService;

    /**
     * @param $shippingService
     * @return $this
     */
    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ShippingService
     */
    public function getShippingService()
    {
        return $this->shippingService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartAddShipment(CoreEvent $event)
    {
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
                    $shippingMethods = []; // r[source_address_key][customer_address_id] = $code
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
                    $event->getRequest()->request->set('shipping_methods', $shippingMethods);

                } elseif (isset($apiRequest[EC::SHIPPING_METHOD])) {

                    $shippingMethod = $apiRequest[EC::SHIPPING_METHOD];

                    if ($this->getShippingService()->getIsMultiShippingEnabled()) {

                        $shippingMethods = []; // r[source_address_key][customer_address_id] = $code

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

                        $event->getRequest()->request->set('shipping_methods', $shippingMethods);
                    } else {
                        $event->getRequest()->request->set(EC::SHIPPING_METHOD, $shippingMethod);
                    }
                }

                break;
            default:

                break;
        }

        // continue base logic

        $success = false;
        $request = $event->getRequest();
        $this->initCart($request);

        // handle multiple shipments, if necessary
        if ($this->getShippingService()->getIsMultiShippingEnabled()) {
            $codes = $request->get('shipping_methods', []); // r[source_address_key][customer_address_id] = $code
            if (is_array($codes) && count($codes)) {
                foreach($codes as $srcAddressKey => $customerAddressIds) {

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

                            $success = true;
                            break;
                        }
                    }
                }
            }
        } else {
            // otherwise, assume a single shipment and shipping method
            $code = $request->get(EC::SHIPPING_METHOD, ''); // single shipping method
            if ($this->getCartService()->hasShippingMethodCode($code)) {

                $rate = $this->getCartService()->getCart()->findShippingMethod('code', $code);

                $shipment = new Shipment();
                $shipment->fromArray($rate->getData());

                $this->getCartService()->removeShipments();
                $this->getCartService()->addShipment($shipment);

                $success = true;
            }
        }

        $event->setSuccess($success);
    }
}
