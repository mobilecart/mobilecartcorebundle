<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\Shipment;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class AddShipment
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class AddShipment
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    public $cartService;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    public $shippingService;

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
        $shippingService = $this->getShippingService();
        $cartService = $this->getCartService()->initCart();
        $cart = $cartService->getCart();
        $cartItems = $cart->getItems();

        $success = false;
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $event->set('format', $format);

        // handle multiple shipments, if necessary
        if ($shippingService->getIsMultiShippingEnabled()) {
            $codes = $request->get('shipping_methods', []); // r[source_address_key][customer_address_id] = $code
            if (is_array($codes) && count($codes)) {
                foreach($codes as $srcAddressKey => $customerAddressIds) {

                    if (!$customerAddressIds) {
                        continue;
                    }

                    foreach($customerAddressIds as $anAddressId => $methodCode) {
                        $anAddressId = $this->getCartService()->unprefixAddressId($anAddressId);
                        if ($rate = $cart->findShippingMethod('code', $methodCode, $anAddressId, $srcAddressKey)) {
                            $productIds = [];
                            if ($cartItems) {
                                foreach($cartItems as $item) {
                                    if ($item->get('customer_address_id', 'main') == $anAddressId
                                        && $item->get('source_address_key', 'main') == $srcAddressKey
                                    ) {
                                        $productIds[] = $item->getProductId();
                                    }
                                }
                            }

                            $shipment = new Shipment();
                            $shipment->fromArray($rate->getData());

                            $cart->unsetShipments($anAddressId, $srcAddressKey)
                                ->addShipment($shipment, $anAddressId, $productIds, $srcAddressKey);

                            $success = true;
                            break;
                        }
                    }
                }
            }
        } else {
            // otherwise, assume a single shipment and shipping method
            $code = $request->get('shipping_method', ''); // single shipping method
            if ($this->getCartService()->hasShippingMethodCode($code)) {

                $rate = $cart->getShippingMethod($cart->findShippingMethodIdx('code', $code));
                $shipment = new Shipment();
                $shipment->fromArray($rate->getData());

                $cart->unsetShipments()
                    ->addShipment($shipment, 'main', $cart->getProductIds());

                $success = true;
            }
        }

        $this->getCartService()->setCart($cart);
        $event->setReturnData('cart', $cart);
        $event->setReturnData('success', $success);
    }
}
