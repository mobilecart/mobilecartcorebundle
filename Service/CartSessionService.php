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
use MobileCart\CoreBundle\Constants\EntityConstants;
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
     * @param CartService $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @return ShippingService
     */
    public function getShippingService()
    {
        return $this->getCartService()->getShippingService();
    }

    /**
     * @return DoctrineEntityService
     */
    public function getEntityService()
    {
        return $this->getShippingService()->getEntityService();
    }

    /**
     * @return CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->getCartService()->getCartTotalService();
    }

    /**
     * @return mixed
     */
    public function getDiscountService()
    {
        return $this->getCartService()->getDiscountService();
    }

    /**
     * @return TaxService
     */
    public function getTaxService()
    {
        return $this->getCartService()->getTaxService();
    }

    /**
     * @return GeographyService
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
     * @return Cart
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
     * @return CurrencyService
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
        $this->addItem($item, $qty);
        return $this;
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
     */
    public function setCustomerEntity($entity)
    {
        $customer = $this->getCustomerInstance();
        $customer->fromArray($entity->getData());
        $customer->setId($entity->getId());
        $addresses = [];
        $addressEntities = $entity->getAddresses();

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

        if ($addressEntities) {
            foreach($addressEntities as $addressEntity) {
                $address = $addressEntity->getData();
                $address['label'] = $address['street'] . ' ' . $address['city'] . ', ' . $address['region'];
                $addresses[] = $address;
            }
        }

        $customer->setAddresses($addresses);
        $this->setCustomer($customer);
        return $this;
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
        return isset($addresses[$addressId])
            ? $addresses[$addressId]
            : null;
    }

    /**
     * Adds a chosen shipping method
     * This activates shipping costs within shopping cart
     *  and allows for multiple shipping methods to be used within the cart
     *
     * @param Shipment $shipment
     * @param $addressId
     * @param array $productIds
     * @return $this
     */
    public function addShipment(Shipment $shipment, $addressId='main', $productIds = [])
    {
        if (!$addressId) {
            $addressId = 'main';
        }
        $shipment->set('customer_address_id', $addressId);
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
     * @return bool
     */
    public function addressHasShipment($addressId)
    {
        return $this->getCart()->addressHasShipment($addressId);
    }

    /**
     * @param $addressId
     * @return string
     */
    public function getShipmentMethod($addressId='main')
    {
        $shipments = $this->getCart()->getShipments();
        if ($shipments) {
            foreach($shipments as $shipment) {
                if ($shipment->get('customer_address_id') == $addressId) {
                    $shipment->getId();
                }
            }
        }

        return '';
    }

    /**
     * @param array $rates
     * @param $addressId
     * @return $this
     */
    public function addRates(array $rates = [], $addressId='main')
    {
        if ($rates) {
            foreach($rates as $code => $rate) {
                $this->addRate($rate, $addressId);
            }
        }

        return $this;
    }

    /**
     * @param array $rates
     * @param $addressId
     * @return $this
     */
    public function setRates(array $rates = [], $addressId='main')
    {
        $this->removeRates($addressId);

        if (!$addressId) {
            $addressId = 'main';
        }

        if ($rates) {
            foreach($rates as $code => $rate) {
                $rate->set('customer_address_id', $addressId);
                $this->addRate($rate, $addressId);
            }
        }

        return $this;
    }

    /**
     * @param $addressId
     * @return $this
     */
    public function removeRates($addressId='main')
    {
        $this->getCart()->unsetShippingMethods($addressId);
        return $this;
    }

    /**
     * @param Rate $rate
     * @param $addressId
     * @return $this
     */
    public function addRate(Rate $rate, $addressId='main')
    {
        $shipment = $this->getShipmentInstance();
        $shipment->fromArray($rate->toArray());
        $this->addShippingMethod($shipment, $addressId);
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
     * @return $this
     */
    public function addShippingMethod(Shipment $shipment, $addressId='main')
    {
        $this->getCart()->addShippingMethod($shipment, $addressId);
        return $this;
    }

    /**
     * @param $addressId
     * @return array
     */
    public function getShippingMethods($addressId='main')
    {
        return $this->getCart()->getShippingMethods($addressId);
    }

    /**
     * Empty both shipments and shipment options
     *
     * @param $addressId
     * @return $this
     */
    public function removeShipments($addressId='main')
    {
        $this->getCart()->unsetShipments($addressId);
        return $this;
    }

    /**
     *
     * @param $addressId
     * @return $this
     */
    public function removeShippingMethods($addressId='main')
    {
        $this->getCart()->unsetShippingMethods($addressId);
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
     * @param $addressId
     * @return $this
     */
    public function collectShippingMethods($addressId='main')
    {
        if (!$this->getShippingService()->getIsShippingEnabled()) {
            return $this;
        }

        $this->removeShipments($addressId)
            ->removeShippingMethods($addressId);

        $customer = $this->getCustomer();
        $postcode = $customer->getShippingPostcode();
        $countryId = $customer->getShippingCountryId();
        $region = $customer->getShippingRegion();

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

        $cartItems = [];
        if ($this->hasItems()) {
            foreach($this->getItems() as $item) {
                if ($item->get('customer_address_id') == $addressId) {
                    $cartItems[] = $item;
                }
            }
        }

        $request = new RateRequest();
        $request->fromArray([
            'to_array'    => 0,
            'include_all' => 0,
            'postcode'    => $postcode,
            'country_id'  => $countryId,
            'region'      => $region,
            'cart_items'  => $cartItems,
        ]);

        $rates = $this->getShippingService()->collectShippingRates($request);
        $this->setRates($rates, $addressId);

        if (!$this->addressHasShipment($addressId)
            && count($rates)
        ) {
            $rates = array_values($rates);
            $rate = $rates[0];
            $shipment = new Shipment();
            $shipment->fromArray($rate->getData());
            $this->addShipment($shipment, $addressId, []);
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
