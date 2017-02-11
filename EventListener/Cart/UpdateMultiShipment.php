<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\CartComponent\Shipment;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Shipping\Rate;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Shipping\RateRequest;

class UpdateMultiShipment
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    protected $shippingService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
    {
        return $this->shippingService;
    }

    public function getCurrencyService()
    {
        return $this->getCartSessionService()->getCartService()->getCartTotalService()->getCurrencyService();
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function onCartUpdateMultiShipment(Event $event)
    {
        $request = $event->getRequest();
        $products = $request->get('product_address', []);
        $cart = $this->getCartSessionService()->getCart();
        $srcAddressKeys = [];
        $srcAddresses = $this->getShippingService()->getSourceAddresses();
        if (!$srcAddresses) {
            throw new \Exception("No Source Addresses are configured.");
        }

        $addressProductIds = []; // r[src_address_key][address_id] => [x,y,z]
        if (is_array($products)
            && count($products)
        ) {

            foreach($cart->getItems() as $item) {

                $srcAddressKey = $item->getSourceAddressKey();

                if (!$srcAddressKey) {
                    $srcAddressKey = 'main';
                }

                $srcAddressKeys[$srcAddressKey] = $srcAddressKey;

                $addressId = isset($productIds[$item->getProductId()])
                    ? $productIds[$item->getProductId()]
                    : null;

                if (is_null($addressId)) {
                    continue;
                }

                if (!isset($addressProductIds[$srcAddressKey])) {
                    $addressProductIds[$srcAddressKey] = [];
                }

                if (!isset($addressProductIds[$srcAddressKey][$addressId])) {
                    $addressProductIds[$srcAddressKey][$addressId] = [];
                }

                $addressProductIds[$srcAddressKey][$addressId][] = $item->getProductId();
            }
        }

        if ($addressProductIds) {

            // take note of previous selections, and set them as default after the new rates are ready
            $shipments = $this->getCartSessionService()->getShipments();
            $defaults = []; // r[src_address_key][address_id] = shipping_method_code
            if ($shipments) {
                foreach($shipments as $shipment) {
                    $customerAddressId = $shipment->get('customer_address_id', 'main');
                    $srcAddressKey = $shipment->get('source_address_key', 'main');
                    $defaults[$srcAddressKey][$customerAddressId] = $shipment->getCode();
                }
            }

            foreach($addressProductIds as $srcAddressKey => $addressIds) {

                if (!$addressIds) {
                    continue;
                }

                foreach($addressIds as $addressId => $productIds) {

                    if (!$productIds) {
                        continue;
                    }

                    $this->getCartSessionService()
                        ->removeShipments($addressId, $srcAddressKey)
                        ->removeShippingMethods($addressId, $srcAddressKey);

                    $postcode = '';
                    $countryId = '';
                    $region = '';

                    if ($addressId == 'main') {

                        $customer = $this->getCartSessionService()->getCustomer();
                        $postcode = $customer->getShippingPostcode();
                        $countryId = $customer->getShippingCountryId();
                        $region = $customer->getShippingRegion();

                    } else {

                        $address = $this->getEntityService()->find(EntityConstants::CUSTOMER_ADDRESS, $addressId);
                        if (!$address) {
                            continue;
                        }

                        $postcode = $address->getPostcode();
                        $countryId = $address->getCountryId();
                        $region = $address->getRegion();
                    }

                    $cartItems = [];
                    $items = $this->getCartSessionService()->getItems();
                    if ($items) {
                        foreach($items as $item) {
                            if (in_array($item->getProductId(), $productIds)) {
                                $cartItems[] = $item;
                                $item->set('customer_address_id', $addressId);
                                $item->set('source_address_key', $srcAddressKey);
                            }
                        }
                    }

                    // create rate request
                    $rateRequest = $this->getShippingService()->createRateRequest($srcAddressKey, $cartItems);
                    $rateRequest->addData([
                        'to_array' => 1,
                        'hide_costs' => 1,
                        'postcode' => $postcode,
                        'country_id' => $countryId,
                        'region' => $region,
                        'customer_address_id' => $addressId,
                    ]);

                    // collect shipping methods for the address
                    $rates = $this->getCartSessionService()
                        ->getShippingService()
                        ->collectShippingRates($request);

                    if ($rates) {
                        foreach($rates as $idx => $rate) {
                            if (is_array($rate)) {
                                $newRate = new Rate();
                                $newRate->fromArray($rate);
                                $newRate->set('customer_address_id', $addressId);
                                $newRate->set('source_address_key', $srcAddressKey);
                                $rates[$idx] = $newRate;
                            }
                        }
                    }

                    // add the shipping methods to the cart
                    $this->getCartSessionService()->setRates($rates, $addressId, $srcAddressKey);
                    if ($rates) {
                        $rate = $rates[0];
                        if (isset($defaults[$srcAddressKey][$addressId])) {
                            $code = $defaults[$srcAddressKey][$addressId];
                            foreach($rates as $aRate) {
                                if ($aRate->getCode() == $code) {
                                    $rate = $aRate;
                                    break;
                                }
                            }
                        }
                        $shipment = new Shipment();
                        $shipment->fromArray($rate->getData());
                        if (!$this->getCartSessionService()->addressHasShipment($addressId, $srcAddressKey)) {
                            $this->getCartSessionService()->addShipment($shipment, $addressId, $srcAddressKey);
                        }
                    }
                }
            }

            // Collect Totals
            $cart = $this->getCartSessionService()->collectTotals()->getCart();

            $cartId = $cart->getId();

            $customerId = $cart->getCustomer()->getId();
            $customerEntity = false;

            $cartEntity = $cartId
                ? $this->getEntityService()->find(EntityConstants::CART, $cartId)
                : $this->getEntityService()->getInstance(EntityConstants::CART);

            if (!$cartId) {

                $cartEntity->setJson($cart->toJson())
                    ->setCreatedAt(new \DateTime('now'));

                if ($customerId) {

                    $customerEntity = $this->getEntityService()
                        ->find(EntityConstants::CUSTOMER, $customerId);

                    if ($customerEntity) {
                        $cartEntity->setCustomer($customerEntity);
                    }
                }

                $this->getEntityService()->persist($cartEntity);
                $cartId = $cartEntity->getId();
                $cart->setId($cartId);
            }

            $cartItemEntities = $cartEntity->getCartItems();
            if ($cartItemEntities) {
                foreach($cartItemEntities as $cartItemEntity) {
                    if (isset($products[$cartItemEntity->getProductId()])) {
                        $addressId = $products[$cartItemEntity->getProductId()];
                        if ($addressId == 'main') {
                            $addressId = null; // can't save 'main' in an integer field
                        }
                        $cartItem = $cart->findItem('product_id', $cartItemEntity->getProductId());
                        $cartItemEntity->setCustomerAddressId($addressId)
                            ->setJson($cartItem->toJson());

                        $this->getEntityService()->persist($cartItemEntity);

                        $cartItem->setId($cartItemEntity->getId());
                    }
                }
            }

            $currencyService = $this->getCurrencyService();
            $baseCurrency = $currencyService->getBaseCurrency();
            $currency = $cart->getCurrency();
            if (!$currency) {
                $currency = $baseCurrency;
            }

            // set totals
            $totals = $cart->getTotals();
            foreach($totals as $total) {
                switch($total->getKey()) {
                    case 'items':
                        $cartEntity->setBaseItemTotal($total->getValue());
                        if ($baseCurrency == $currency) {
                            $cartEntity->setItemTotal($total->getValue());
                        } else {
                            $cartEntity->setItemTotal($currencyService->convert($total->getValue(), $currency));
                        }
                        break;
                    case 'shipments':
                        $cartEntity->setBaseShippingTotal($total->getValue());
                        if ($baseCurrency == $currency) {
                            $cartEntity->setShippingTotal($total->getValue());
                        } else {
                            $cartEntity->setShippingTotal($currencyService->convert($total->getValue(), $currency));
                        }
                        break;
                    case 'tax':
                        $cartEntity->setBaseTaxTotal($total->getValue());
                        if ($baseCurrency == $currency) {
                            $cartEntity->setTaxTotal($total->getValue());
                        } else {
                            $cartEntity->setTaxTotal($currencyService->convert($total->getValue(), $currency));
                        }
                        break;
                    case 'discounts':
                        $cartEntity->setBaseDiscountTotal($total->getValue());
                        if ($baseCurrency == $currency) {
                            $cartEntity->setDiscountTotal($total->getValue());
                        } else {
                            $cartEntity->setDiscountTotal($currencyService->convert($total->getValue(), $currency));
                        }
                        break;
                    case 'grand_total':
                        $cartEntity->setBaseTotal($total->getValue());
                        if ($baseCurrency == $currency) {
                            $cartEntity->setTotal($total->getValue());
                        } else {
                            $cartEntity->setTotal($currencyService->convert($total->getValue(), $currency));
                        }
                        break;
                    default:
                        // no-op
                        break;
                }
            }

            $cartEntity->setJson($cart->toJson());
            // update Cart in database
            $this->getEntityService()->persist($cartEntity);

            $this->getCartSessionService()->setCart($cart);
        }
    }
}
