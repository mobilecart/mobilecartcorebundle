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
    public function setCart(Cart $cart)
    {
        $this->session->set($this->getSessionKey(), $cart);
        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        if (!$this->session->get($this->getSessionKey(), false)) {
            $this->setCart($this->getCartInstance());
        }
        return $this->session->get($this->getSessionKey());
    }

    /**
     * @return $this
     */
    public function resetCart()
    {
        $this->setCart($this->getCartInstance());
        return $this;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function initCart(Cart $cart = null)
    {
        if ($cart instanceof Cart) {
            return $this->setCart($cart);
        } else if ($this->getCart() instanceof Cart) {
            return $this;
        }

        $cart = $this->getCartInstance();
        $this->setCart($cart);
        return $this;
    }

    /**
     * @param string $json
     * @return Cart
     */
    public function initCartJson($json)
    {
        $cart = $this->getCartInstance();
        $cart->importJson($json);
        $this->setCart($cart);
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
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
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
     * @param Item|int $item
     * @param int $qty
     * @return $this
     */
    public function addItem($item, $qty = 1)
    {
        $item->setQty($qty);
        $this->getCart()->addItem($item);
        return $this;
    }

    /**
     * @return mixed
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
    public function addProduct($product, $qty = 1, $parentOptions = [])
    {
        $item = $this->createCartItem($product, $parentOptions);
        $this->addItem($item, $qty);
        return $this;
    }

    /**
     * @param $product
     * @param array $parentOptions
     * @return Item
     * @throws \InvalidArgumentException
     */
    public function createCartItem($product, $parentOptions = [])
    {
        if (is_null($product)) {
            throw new \InvalidArgumentException("Product cannot be null");
        }

        $item = $this->getItemInstance();
        $data = $product->getData();
        $data['product_id'] = $data['id'];
        unset($data['id']);
        $item->fromArray($data);
        if ($parentOptions) {
            $item->set('parent_options', $parentOptions);
        }
        if ($pimages = $product->getImages()) {
            $images = [];
            foreach($pimages as $image) {
                $images[] = $image->getData();
            }
            $item->setImages($images);
        }
        if ($tierPrices = $product->getTierPrices()) {
            $tierData = [];
            foreach($tierPrices as $tierPrice) {
                $tierData[] = [
                    'qty' => $tierPrice->getQty(),
                    'price' => $tierPrice->getPrice(),
                ];
            }

            $item->setTierPrices($tierData)
                ->setOrigPrice($product->getPrice());
        }
        return $item;
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
        return $this;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->getCart()->setCustomer($customer);
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
     * @return array
     */
    public function getCustomerAddresses()
    {
        $addresses = $this->getCustomer()->getAddresses();
        if ($addresses) {
            foreach($addresses as $k => $v) {

                if ($v instanceof \stdClass) {
                    $v = get_object_vars($v);
                }

                if (is_object($v)) {
                    $v = $v->getData();
                }

                $v['label'] = $v['street'] . ' ' . $v['city'] . ', ' . $v['region'];
                $addresses[$k] = new ArrayWrapper($v);
            }
        }

        return $addresses;
    }

    /**
     * @param $entity
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCustomerEntity($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException("Entity cannot be null");
        }

        $customer = $this->getCustomerInstance();
        $customer->fromArray($entity->getData());
        $customer->setId($entity->getId());
        $addresses = [];
        $addressEntities = $entity->getAddresses();
        $postcodes = [];

        $addresses[] = [
            'id' => 'main',
            'label' => "{$entity->getShippingStreet()} {$entity->getShippingCity()}, {$entity->getShippingRegion()}",
            'name' => $entity->getShippingName(),
            'company' => $entity->getShippingCompany(),
            'street' => $entity->getShippingStreet(),
            'city' => $entity->getShippingCity(),
            'region' => $entity->getShippingRegion(),
            'postcode' => $entity->getShippingPostcode(),
            'country_id' => $entity->getShippingCountryId(),
            'phone' => $entity->getShippingPhone(),
        ];

        $postcodes[] = $entity->getShippingPostcode();

        if ($addressEntities) {
            foreach($addressEntities as $addressEntity) {
                $address = $addressEntity->getData();
                $address['label'] = $address['street'] . ' ' . $address['city'] . ', ' . $address['region'];
                $addresses[] = $address;
                $postcodes[] = $addressEntity->getPostcode();
            }
        }
        $customer->setAddresses($addresses);

        $groups = $entity->getGroups();
        $groupNames = [];
        if ($groups) {
            foreach($groups as $group) {
                $groupNames[] = $group->getName();
            }
        }
        $customer->setGroups($groupNames);

        $this->setCustomer($customer);

        $cartCustomerId = $this->getCart()->getCustomerId();
        $this->getLogger()->info("CartSession : setCustomerEntity() : Customer ID : {$entity->getId()}, Cart Customer ID: {$cartCustomerId} , postcodes: " . implode(', ', $postcodes));

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
        $addresses = $this->getCustomerAddresses();

        if ($addressId != 'main' && !is_numeric($addressId)) {
            $addressId = (int) str_replace('address_', '', $addressId);
        }

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
     * Adds a chosen shipping method
     * This activates shipping costs within shopping cart
     *  and allows for multiple shipping methods to be used within the cart
     *
     * @param Shipment $shipment
     * @param $addressId
     * @param $srcAddressKey
     * @param array $productIds
     * @return $this
     */
    public function addShipment(Shipment $shipment, $addressId='main', $productIds = [], $srcAddressKey='main')
    {
        if (!$addressId) {
            $addressId = 'main';
        }
        $shipment->set('customer_address_id', $addressId);
        $shipment->set('source_address_key', $srcAddressKey);
        $shipment->set('product_ids', $productIds);
        $this->getCart()->addShipment($shipment);
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
                if ($shipment->get('customer_address_id') == $addressId
                    && $shipment->get('source_address_key') == $srcAddressKey
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

        return $this;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeRates($addressId='main', $srcAddressKey='main')
    {
        $this->getCart()->unsetShippingMethods($addressId, $srcAddressKey);
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
     * Empty both shipments and shipment options
     *
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShipments($addressId='main', $srcAddressKey='main')
    {
        $this->getCart()->unsetShipments($addressId, $srcAddressKey);
        return $this;
    }

    /**
     *
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShippingMethods($addressId='main', $srcAddressKey='main')
    {
        $this->getCart()->unsetShippingMethods($addressId, $srcAddressKey);
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
        return $this;
    }

    /**
     * @return $this
     */
    public function collectTotals()
    {
        $totals = $this->getCartTotalService()
            ->setIsShippingEnabled($this->getShippingService()->getIsShippingEnabled())
            ->setIsTaxEnabled($this->getTaxService()->getIsTaxEnabled())
            ->setCart($this->getCart())
            ->collectTotals()
            ->getTotals();

        $this->setTotals($totals); // for saving state
        return $this;
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function setTotals(array $totals)
    {
        $this->getCart()->setTotals($totals); // for saving state
        return $this;
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        return $this->getCart()->getTotals();
    }

    /**
     * @param string $addressId
     * @param string $srcAddressKey
     * @return RateRequest
     */
    public function createRateRequest($addressId='main', $srcAddressKey='main')
    {
        $customer = $this->getCustomer();

        $cartItems = [];
        if ($this->hasItems()) {
            foreach($this->getItems() as $item) {
                if ($item->get('customer_address_id') == $addressId
                    && $item->get('source_address_key') == $srcAddressKey
                ) {
                    $cartItems[] = $item;
                }
            }
        }

        $rateRequest = $this->getShippingService()->createRateRequest($srcAddressKey, $cartItems);

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

        $rateRequest->addData([
            'postcode' => $postcode,
            'country_id' => $countryId,
            'region' => $region,
        ]);

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

        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if ($addressId == '*') {

            // loop cart items, get source address keys and customer address IDs
            $addressProductIds = [];
            foreach($items as $item) {

                $srcAddressKey = $item->getSourceAddressKey();
                if (!$srcAddressKey) {
                    $srcAddressKey = 'main';
                }

                $addressId = $item->getCustomerAddressId();
                if (!isset($addressProductIds[$srcAddressKey])) {
                    $addressProductIds[$srcAddressKey] = [];
                }

                if (!isset($addressProductIds[$srcAddressKey][$addressId])) {
                    $addressProductIds[$srcAddressKey][$addressId] = [];
                }

                $addressProductIds[$srcAddressKey][$addressId][] = $item->getProductId();
            }

            if ($addressProductIds) {
                foreach($addressProductIds as $srcAddressKey => $addressIds) {

                    if (!$addressIds) {
                        continue;
                    }

                    foreach($addressIds as $addressId => $productIds) {
                        $this->reloadShipments($addressId, $srcAddressKey);
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
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        $customerId = ($this->getCart()->getCustomer() && $this->getCart()->getCustomer()->getId())
            ? $this->getCart()->getCustomer()->getId()
            : 0;

        $postcodes = [];
        if ($customerId) {
            $addresses = $this->getCart()->getCustomer()->getAddresses();
            if ($addresses) {
                foreach($addresses as $address) {
                    $postcodes[] = $address->getPostcode();
                }
            }
        }
        $this->getLogger()->info("CartSession : reloadShipments() : Cart Customer ID: {$customerId} , postcodes: " . implode(', ', $postcodes));

        // get current shipment method
        $currentShipment = $this->getCart()->getAddressShipment($addressId, $srcAddressKey);

        $this->removeShipments($addressId, $srcAddressKey)
            ->removeShippingMethods($addressId, $srcAddressKey);

        $request = $this->createRateRequest($addressId, $srcAddressKey);
        $rates = [];
        try {
            $rates = $this->getShippingService()->collectShippingRates($request);
            $this->getLogger()->info("CartSession : reloadShipments() : Shipping Rates Successful for Customer ID : {$customerId}");
        } catch(\Exception $e) {
            $this->getLogger()->error("CartSession : reloadShipments() : Shipping Exception for Customer ID : {$customerId} : {$e->getMessage()}");
        }

        $this->setRates($rates, $addressId, $srcAddressKey);

        // add first rate as a shipment
        if (!$this->addressHasShipment($addressId, $srcAddressKey)) {
            if (count($rates)) {
                $rates = array_values($rates);
                $rate = $rates[0];
                if ($currentShipment) {
                    foreach($rates as $idx => $aRate) {
                        if ($aRate->getCode() == $currentShipment->getCode()) {
                            $rate = $rates[$idx];
                            break;
                        }
                    }
                }

                $shipment = new Shipment();
                $shipment->fromArray($rate->getData());

                $productIds = [];
                if ($request->getCartItems()) {
                    foreach($request->getCartItems() as $item) {
                        $productIds[] = $item->getProductId();
                        if ($cartItem = $this->getCart()->findItem('id', $item->getId())) {
                            $cartItem->set('customer_address_id', $addressId);
                            $cartItem->set('source_address_key', $srcAddressKey);
                        }
                    }
                }

                $this->addShipment($shipment, $addressId, $productIds, $srcAddressKey);

                $this->getLogger()->info("CartSession : reloadShipments() : Added Default Shipment for Customer ID : {$customerId}");
            } else {
                $this->getLogger()->error("CartSession : reloadShipments() : No rates for Customer ID : {$customerId}");
            }
        }

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
     * @return array
     */
    public function collectPaymentMethods()
    {
        $methodRequest = new CollectPaymentMethodRequest();
        // todo: populate methodRequest : check if shipping is enabled, etc

        // dispatch event
        $event = new FilterPaymentMethodCollectEvent();
        $event->setCollectPaymentMethodRequest($methodRequest);

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::PAYMENT_METHOD_COLLECT, $event);

        return $event->getMethods();
    }

    /**
     * @param array $methodCodes
     * @return $this
     */
    public function setPaymentMethodCodes(array $methodCodes)
    {
        $this->getCart()->setPaymentMethodCodes($methodCodes);
        return $this;
    }
}
