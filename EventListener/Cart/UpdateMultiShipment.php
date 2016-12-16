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
    protected $entityService;

    protected $cartSessionService;

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

        $addressProductIds = []; // r[address_id] => [x,y,z]
        if (is_array($products) && count($products)) {
            foreach($products as $productId => $addressId) {
                if (!isset($addressProductIds[$addressId])) {
                    $addressProductIds[$addressId] = [];
                }
                $addressProductIds[$addressId][] = $productId;
            }
        }

        if ($addressProductIds) {

            $this->getCartSessionService()
                ->removeShipments('')
                ->removeShippingMethods('');

            foreach($addressProductIds as $addressId => $productIds) {

                if (!$productIds) {
                    continue;
                }

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
                        }
                    }
                }

                // create rate request
                $request = new RateRequest();
                $request->fromArray([
                    'to_array' => 1,
                    'hide_costs' => 1,
                    'postcode' => $postcode,
                    'country_id' => $countryId,
                    'region' => $region,
                    'cart_items' => $cartItems,
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
                            $rates[$idx] = $newRate;
                        }
                    }
                }

                // add the shipping methods to the cart
                $this->getCartSessionService()->setRates($rates, $addressId);
                if ($rates) {
                    $rate = $rates[0];
                    $shipment = new Shipment();
                    $shipment->fromArray($rate->getData());
                    if (!$this->getCartSessionService()->addressHasShipment($addressId)) {
                        $this->getCartSessionService()->addShipment($shipment, $addressId);
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
                            $addressId = null;
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
