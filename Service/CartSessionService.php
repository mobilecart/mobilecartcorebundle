<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Shipping\Rate;
use MobileCart\CoreBundle\CartComponent\Cart;
use MobileCart\CoreBundle\CartComponent\Item;
use MobileCart\CoreBundle\CartComponent\Customer;
use MobileCart\CoreBundle\CartComponent\CustomerAddress;
use MobileCart\CoreBundle\CartComponent\Shipment;
use MobileCart\CoreBundle\CartComponent\Discount;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Shipping\RateRequest;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use MobileCart\CoreBundle\Event\Payment\FilterPaymentMethodCollectEvent;

/**
 * Class CartSessionService
 * @package MobileCart\CoreBundle\Service
 *
 * This service manages the shopping cart stored in session
 */
class CartSessionService
{
    /**
     * @var bool
     */
    protected $isApiRequest = false;

    /**
     * @var bool
     */
    protected $isAdminUser = false;

    /**
     * @var mixed
     */
    protected $session;

    /**
     * @var string
     */
    protected $sessionKey = 'cart';

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param $isApiRequest
     * @return $this
     */
    public function setIsApiRequest($isApiRequest)
    {
        $this->isApiRequest = (bool) $isApiRequest;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsApiRequest()
    {
        return (bool) $this->isApiRequest;
    }

    /**
     * @param $isAdminUser
     * @return $this
     */
    public function setIsAdminUser($isAdminUser)
    {
        $this->isAdminUser = (bool) $isAdminUser;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdminUser()
    {
        return (bool) $this->isAdminUser;
    }

    /**
     * @param $session
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
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
     * @param $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ShippingService
     */
    public function getShippingService()
    {
        return $this->getCartService()->getShippingService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    public function getEntityService()
    {
        return $this->getShippingService()->getEntityService();
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
     * @return \MobileCart\CoreBundle\Service\TaxService
     */
    public function getTaxService()
    {
        return $this->getCartService()->getTaxService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\GeographyService
     */
    public function getGeographyService()
    {
        return $this->getCartService()->getGeographyService();
    }

    /**
     * @param $sessionKey
     * @return $this
     */
    public function setSessionKey($sessionKey)
    {
        $this->sessionKey = $sessionKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setSessionCart(Cart $cart)
    {
        $this->session->set($this->getSessionKey(), $cart);
        return $this;
    }

    /**
     * @return Cart
     */
    public function getSessionCart()
    {
        return $this->session->get($this->getSessionKey());
    }

    /**
     * @return $this
     */
    public function updateSessionCart()
    {
        if (!$this->getIsApiRequest()) {
            $this->session->set($this->getSessionKey(), $this->getCartService()->getCart());
        }
        return $this;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart)
    {
        $this->getCartService()->setCart($cart);
        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->getCartService()->getCart();
    }

    /**
     * @return $this
     */
    public function resetCart()
    {
        $cart = $this->getCartInstance();
        $cart->setCurrency($this->getCurrencyService()->getBaseCurrency());
        $this->setCart($cart);
        return $this;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function initCart(Cart $cart = null)
    {
        if ($cart instanceof Cart) {
            $this->setCart($cart);
            $this->updateSessionCart();
            return $this;
        } elseif ($this->getCartService()->getCart() instanceof Cart) {
            $this->updateSessionCart();
            return $this;
        } elseif ($this->getSessionCart() instanceof Cart) {
            $this->setCart($this->getSessionCart());
            return $this;
        }

        $this->resetCart();
        return $this;
    }

    /**
     * @param string $json
     * @return $this
     */
    public function initCartJson($json)
    {
        $cart = $this->getCartInstance();
        $cart->importJson($json);
        $this->setCart($cart);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return Cart
     */
    public function getCartInstance()
    {
        return $this->getCartService()->getCartInstance();
    }

    /**
     * @return Customer
     */
    public function getCustomerInstance()
    {
        return $this->getCartService()->getCustomerInstance();
    }

    /**
     * @return Item
     */
    public function getItemInstance()
    {
        return $this->getCartService()->getItemInstance();
    }

    /**
     * @return Discount
     */
    public function getDiscountInstance()
    {
        return $this->getCartService()->getDiscountInstance();
    }

    /**
     * @return Shipment
     */
    public function getShipmentInstance()
    {
        return $this->getCartService()->getShipmentInstance();
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->getCart()->setCurrency($currency);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        if (!$this->getCart()) {
            return $this->getBaseCurrency();
        }
        return $this->getCart()->getCurrency();
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->getCurrencyService()->getBaseCurrency();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCartTotalService()->getCurrencyService();
    }

    /**
     * @param Item $item
     * @return $this
     */
    public function addItem(Item $item)
    {
        $this->getCart()->addItem($item);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\CartComponent\Item[]
     */
    public function getItems()
    {
        return $this->getCart()->getItems();
    }

    /**
     * @return $this
     */
    public function removeItems()
    {
        $this->getCart()->unsetItems();
        $this->removeShipments(); // need to remove shipments if we dont have items
        return $this;
    }

    /**
     * @return mixed
     */
    public function hasItems()
    {
        return $this->getCart()->hasItems();
    }

    /**
     * @param $productId
     * @return bool
     */
    public function hasProductId($productId)
    {
        return is_numeric($this->getCart()->findItemIdx('product_id', $productId));
    }

    /**
     * @param $sku
     * @return bool
     */
    public function hasSku($sku)
    {
        return is_numeric($this->getCart()->findItemIdx('sku', $sku));
    }

    /**
     * @param $product
     * @param int $qty
     * @param array $parentOptions
     * @return $this
     */
    public function addProduct($product, $qty = 1, array $parentOptions = [])
    {
        $item = $this->getCartService()->convertProductToItem($product, $parentOptions, $qty);
        $this->addItem($item);
        return $this;
    }

    /**
     * @param \MobileCart\CoreBundle\Entity\Product $product
     * @param array $parentOptions
     * @param int $qty
     * @return Item
     * @throws \InvalidArgumentException
     */
    public function createCartItem(\MobileCart\CoreBundle\Entity\Product $product, array $parentOptions = [], $qty = 1)
    {
        return $this->getCartService()->convertProductToItem($product, $parentOptions, $qty);
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->getCart()->getProductIds();
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function removeProductId($productId)
    {
        $this->getCart()->removeProductId($productId);
        if (!$this->getCart()->hasItems()) {
            $this->removeShipments();
        }
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param $itemId
     * @return $this
     */
    public function removeItemId($itemId)
    {
        $this->getCart()->removeItemId($itemId);
        if (!$this->getCart()->hasItems()) {
            $this->removeShipments();
        }
        $this->updateSessionCart();
        return $this;
    }

    /**
     * Update qty on item already in cart
     *
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function setProductQty($productId, $qty)
    {
        $this->getCart()->setProductQty($productId, $qty);
        if (!$this->getCart()->hasItems()) {
            $this->removeShipments();
        }
        $this->updateSessionCart();
        return $this;
    }

    /**
     * Add qty on item already in cart
     *
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function addProductQty($productId, $qty)
    {
        $this->getCart()->addProductQty($productId, $qty);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->getCart()->setCustomer($customer);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->getCart()->getCustomer();
    }

    /**
     * @return array|CustomerAddress[]
     */
    public function getCustomerAddresses()
    {
        return $this->getCustomer()->getAddresses();
    }

    /**
     * @param \MobileCart\CoreBundle\Entity\Customer $entity
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCustomerEntity(\MobileCart\CoreBundle\Entity\Customer $entity)
    {
        $this->getCartService()->setCustomerEntity($entity);
        $this->setCustomer($this->getCartService()->convertCustomerEntity($entity));
        return $this;
    }

    /**
     * @param $groupName
     * @return bool
     */
    public function customerHasGroup($groupName)
    {
        $groups = $this->getCustomer()->getGroups();
        if (!is_array($groups)) {
            $groups = [];
        }
        return in_array($groupName, $groups);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        $customer = $this->getCart()->getCustomer();
        return ($customer instanceof Customer)
            ? $customer->getId()
            : 0;
    }

    /**
     * @param string $addressId
     * @return string
     */
    public function addressLabel($addressId='main')
    {
        return $this->getCart()->addressLabel($addressId);
    }

    /**
     * @param string $addressId
     * @return null
     */
    public function getCustomerAddress($addressId='main')
    {
        $addressId = $this->getCartService()->unprefixAddressId($addressId);
        $addresses = $this->getCustomerAddresses();
        if ($addresses) {
            foreach($addresses as $address) {
                if ($address->getId() == $addressId) {
                    return $address;
                }
            }
        }

        return null;
    }

    /**
     * @param Shipment $shipment
     * @return $this
     */
    public function addShipment(Shipment $shipment)
    {
        $this->getCart()->addShipment($shipment);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShipments()
    {
        return $this->getCart()->getShipments();
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function addressHasShipment($addressId, $srcAddressKey='main')
    {
        return $this->getCart()->addressHasShipment($addressId, $srcAddressKey);
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return string
     */
    public function getShipmentMethod($addressId='main', $srcAddressKey='main')
    {
        $shipments = $this->getCart()->getShipments();
        if ($shipments) {
            foreach($shipments as $shipment) {
                if ($shipment->getCustomerAddressId() == $addressId
                    && $shipment->getSourceAddressKey() == $srcAddressKey
                ) {
                    $shipment->getId();
                }
            }
        }

        return '';
    }

    /**
     * @param array $rates
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function addRates(array $rates = [], $addressId='main', $srcAddressKey='main')
    {
        if ($rates) {
            foreach($rates as $code => $rate) {
                $this->addRate($rate, $addressId, $srcAddressKey);
            }
        }

        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param array $rates
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function setRates(array $rates = [], $addressId='main', $srcAddressKey='main')
    {
        $this->removeRates($addressId, $srcAddressKey);

        if (!$addressId) {
            $addressId = 'main';
        }

        if ($rates) {
            foreach($rates as $code => $rate) {
                $rate->set('customer_address_id', $addressId);
                $rate->set('source_address_key', $srcAddressKey);
                $this->addRate($rate, $addressId, $srcAddressKey);
            }
        }

        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeRates($addressId='', $srcAddressKey='main')
    {
        $this->getCart()->unsetShippingMethods($addressId, $srcAddressKey);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param Rate $rate
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function addRate(Rate $rate, $addressId='main', $srcAddressKey='main')
    {
        $shipment = $this->getShipmentInstance();
        $shipment->fromArray($rate->toArray());
        $this->addShippingMethod($shipment, $addressId, $srcAddressKey);
        return $this;
    }

    /**
     * Add a shipping method _option_ to the cart
     * This does not activate shipping costs within the shopping cart
     * This is an estimated cost, and needs to be stored in the cart
     *  in order to avoid calculating possible shipping options on every page
     *
     * @param Shipment $shipment
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function addShippingMethod(Shipment $shipment, $addressId='main', $srcAddressKey='main')
    {
        $this->getCart()->addShippingMethod($shipment, $addressId, $srcAddressKey);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return array
     */
    public function getShippingMethods($addressId='main', $srcAddressKey='main')
    {
        return $this->getCart()->getShippingMethods($addressId, $srcAddressKey);
    }

    /**
     * @return array
     */
    public function getAllShippingMethods()
    {
        return $this->getCart()->getAllShippingMethods();
    }

    /**
     * Empty both shipments and shipment options
     *
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShipments($addressId='', $srcAddressKey='main')
    {
        $this->getCart()->unsetShipments($addressId, $srcAddressKey);
        $this->updateSessionCart();
        return $this;
    }

    /**
     *
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShippingMethods($addressId='', $srcAddressKey='main')
    {
        $this->getCart()->unsetShippingMethods($addressId, $srcAddressKey);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param string|int $key
     * @param bool $isKey
     * @return $this
     */
    public function removeShipment($key, $isKey = true)
    {
        $this->getCart()->unsetShipment($key, $isKey);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return $this
     */
    public function collectTotals()
    {
        $totals = $this->getCartService()
            ->collectTotals()
            ->getTotals();

        $this->setTotals($totals); // for saving state
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function setTotals(array $totals)
    {
        $this->getCart()->setTotals($totals);
        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        if (!$this->getCart()->getTotals()) {
            $this->collectTotals();
        }
        return $this->getCart()->getTotals();
    }

    /**
     * @param $addressId
     * @return string
     */
    public function prefixAddressId($addressId)
    {
        return $this->getCart()->prefixAddressId($addressId);
    }

    /**
     * @param $addressId
     * @return int
     */
    public function unprefixAddressId($addressId)
    {
        return $this->getCart()->unprefixAddressId($addressId);
    }

    /**
     * @param string $addressId
     * @param string $srcAddressKey
     * @return RateRequest
     */
    public function createRateRequest($addressId='main', $srcAddressKey='main')
    {
        $addressId = $this->unprefixAddressId($addressId);

        $customer = $this->getCustomer();

        $cartItems = [];
        $addtlPrice = 0.0;
        if ($this->hasItems()) {
            foreach($this->getItems() as $item) {

                if ($item->getCustomerAddressId() == $addressId
                    && $item->getSourceAddressKey() == $srcAddressKey
                ) {

                    if ($item->getIsFlatShipping()) {
                        $addtlPrice += ($item->getQty() * (float) $item->getFlatShippingPrice());
                        continue;
                    }

                    $cartItems[] = $item;
                }
            }
        }

        $rateRequest = $this->getShippingService()->createRateRequest($srcAddressKey, $cartItems, $addtlPrice);

        // default to 'main' shipping address
        $postcode = $customer->getShippingPostcode();
        $countryId = $customer->getShippingCountryId();
        $region = $customer->getShippingRegion();

        // if the shipping address is stored in customer_address and has an ID
        if (is_numeric($addressId) && $customer->getAddresses()) {
            foreach($customer->getAddresses() as $address) {
                if ($address->getId() == $addressId) {
                    $postcode = $address->getPostcode();
                    $countryId = $address->getCountryId();
                    $region = $address->getRegion();
                    break;
                }
            }
        }

        $rateRequest->setDestPostcode($postcode)
            ->setDestCountryId($countryId)
            ->setDestRegion($region);

        return $rateRequest;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function collectShippingMethods($addressId='main', $srcAddressKey='main')
    {
        if (!$this->getShippingService()->getIsShippingEnabled()) {
            return $this;
        }

        $items = $this->getCart()->getItems();
        if (!$items) {
            return $this;
        }

        $addressId = $this->unprefixAddressId($addressId);
        $destAddressKey = $this->prefixAddressId($addressId);

        if (in_array($destAddressKey, ['', '*'])) {

            // loop cart items, get source address keys and customer address IDs
            $addressProductIds = [];
            foreach($items as $item) {

                $srcAddressKey = $item->getSourceAddressKey();

                //$anAddressId = $item->getCustomerAddressId();
                if (!isset($addressProductIds[$srcAddressKey])) {
                    $addressProductIds[$srcAddressKey] = [];
                }

                if (!isset($addressProductIds[$srcAddressKey][$destAddressKey])) {
                    $addressProductIds[$srcAddressKey][$destAddressKey] = [];
                }

                $addressProductIds[$srcAddressKey][$destAddressKey][] = $item->getProductId();
            }

            if ($addressProductIds) {
                foreach($addressProductIds as $srcAddressKey => $destAddressKeys) {

                    if (!$destAddressKeys) {
                        continue;
                    }

                    foreach($destAddressKeys as $anAddressKey => $productIds) {
                        $this->reloadShipments($anAddressKey, $srcAddressKey);
                    }
                }
            }

        } else {
            $this->reloadShipments($addressId, $srcAddressKey);
        }

        return $this;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function reloadShipments($addressId='main', $srcAddressKey='main')
    {
        $addressId = $this->unprefixAddressId($addressId);
        $destAddressKey = $this->prefixAddressId($addressId);

        $request = $this->createRateRequest($addressId, $srcAddressKey);

        $customerId = $this->getCustomerId();

        $productIds = [];
        $skus = [];
        if ($request->getCartItems()) {
            foreach($request->getCartItems() as $item) {
                $productIds[] = $item->getProductId();
                $skus[] = $item->getSku();
            }
        }

        $request->setProductIds($productIds)
            ->setSkus($skus);

        $postcodes = [];
        if ($customerId) {
            $addresses = $this->getCart()->getCustomer()->getAddresses();
            if ($addresses) {
                foreach($addresses as $address) {
                    $postcodes[] = $address->getPostcode();
                }
            }
        }

        // get current shipment method
        $currentShipment = $this->getCart()->getAddressShipment($addressId, $srcAddressKey);

        $this->removeShipments($addressId, $srcAddressKey)
            ->removeShippingMethods($addressId, $srcAddressKey);

        $rates = [];
        try {
            $rates = $this->getShippingService()->collectShippingRates($request);
        } catch(\Exception $e) {
            $this->getLogger()->error("CartSession : reloadShipments() : Shipping Exception for Customer ID : {$customerId} : {$e->getMessage()}");
        }

        $this->setRates($rates, $addressId, $srcAddressKey);

        // add first rate as a shipment
        if (!$this->addressHasShipment($addressId, $srcAddressKey)) {
            if (count($rates)) {
                $rates = array_values($rates);
                /** @var \MobileCart\CoreBundle\Shipping\Rate $rate */
                $rate = $rates[0];
                if ($currentShipment) {
                    /** @var \MobileCart\CoreBundle\Shipping\Rate $aRate */
                    foreach($rates as $idx => $aRate) {
                        if ($aRate->getCode() == $currentShipment->getCode()) {
                            $rate = $rates[$idx];
                            break;
                        }
                    }
                }

                $shipment = new Shipment();
                $shipment->fromArray($rate->getData());
                $shipment->setCustomerAddressId($addressId)
                    ->setSourceAddressKey($srcAddressKey)
                    ->setProductIds($productIds);

                $this->addShipment($shipment);//, $addressId, $productIds, $srcAddressKey);
            } else {
                $this->getLogger()->error("CartSession : reloadShipments() : No rates for Customer ID : {$customerId}");
            }
        }

        $this->updateSessionCart();
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedCountryIds()
    {
        return $this->getCartService()->getAllowedCountryIds();
    }

    /**
     * @param $countryId
     * @return bool
     */
    public function isAllowedCountryId($countryId)
    {
        return in_array($countryId, $this->getAllowedCountryIds());
    }

    /**
     * @return array
     */
    public function getCountryRegions()
    {
        return $this->getGeographyService()->getRegionsByCountries($this->getAllowedCountryIds());
    }

    /**
     * @param array $methodCodes
     * @return $this
     */
    public function setPaymentMethodCodes(array $methodCodes)
    {
        $this->getCart()->setPaymentMethodCodes($methodCodes);
        $this->updateSessionCart();
        return $this;
    }
}
