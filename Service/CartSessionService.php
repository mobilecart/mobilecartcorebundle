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
        $this->initCart();
        $this->getCart()->setCurrency($currency);
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        $this->initCart();
        return $this->getCart()->getCurrency();
    }

    /**
     * @param Item|int $item
     * @param int $qty
     * @return $this
     */
    public function addItem($item, $qty = 1)
    {
        $this->initCart();
        $item->setQty($qty);
        $this->getCart()->addItem($item);
        return $this;
    }

    /**
     * @return $this
     */
    public function removeItems()
    {
        $this->initCart()->getCart()->unsetItems();
        $this->removeShipments(); // need to remove shipments if we dont have items
        return $this;
    }

    /**
     * @return mixed
     */
    public function hasItems()
    {
        return $this->initCart()->getCart()->hasItems();
    }

    /**
     * @param $productId
     * @return bool
     */
    public function hasProductId($productId)
    {
        return is_numeric($this->initCart()->getCart()->findItemIdx('product_id', $productId));
    }

    /**
     * @param $sku
     * @return bool
     */
    public function hasSku($sku)
    {
        return is_numeric($this->initCart()->getCart()->findItemIdx('sku', $sku));
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
        $this->addItem($item, $qty);
        return $this;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        $this->initCart();
        return $this->getCart()->getProductIds();
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function removeProductId($productId)
    {
        $this->initCart();
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
        $this->initCart();
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
        $this->initCart();
        $this->getCart()->addProductQty($productId, $qty);
        return $this;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->initCart();
        $this->getCart()->setCustomer($customer);
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        $this->initCart();
        return $this->getCart()->getCustomer();
    }

    /**
     * @param $entity
     * @return $this
     */
    public function setCustomerEntity($entity)
    {
        $customer = $this->getCustomerInstance();
        $customer->fromArray($entity->getBaseData());
        return $this->setCustomer($customer);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        $this->initCart();
        $customer = $this->getCart()->getCustomer();
        return ($customer instanceof Customer)
            ? $customer->getId()
            : 0;
    }

    /**
     * Adds a chosen shipping method
     * This activates shipping costs within shopping cart
     *  and allows for multiple shipping methods to be used within the cart
     *
     * @param Shipment $shipment
     * @return $this
     */
    public function addShipment(Shipment $shipment)
    {
        $this->initCart();
        $this->getCart()->addShipment($shipment);
        return $this;
    }

    /**
     * @return string
     */
    public function getShipmentMethod()
    {
        $this->initCart();
        $shipments = $this->getCart()->getShipments();
        if ($shipments) {
            $shipment = $shipments[0];
            return $shipment->getId();
        }

        return '';
    }

    /**
     * @param array $rates
     * @return $this
     */
    public function addRates(array $rates = [])
    {
        if ($rates) {
            foreach($rates as $code => $rate) {
                $this->addRate($rate);
            }
        }

        return $this;
    }

    /**
     * @param array $rates
     * @return $this
     */
    public function setRates(array $rates = [])
    {
        $this->removeRates();
        if ($rates) {
            foreach($rates as $code => $rate) {
                $this->addRate($rate);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeRates()
    {
        $this->initCart();
        $this->getCart()->unsetShippingMethods();
        return $this;
    }

    /**
     * @param Rate $rate
     * @return $this
     */
    public function addRate(Rate $rate)
    {
        $shipment = $this->getShipmentInstance();
        $shipment->fromArray($rate->toArray());
        $this->addShippingMethod($shipment);
        return $this;
    }

    /**
     * Add a shipping method _option_ to the cart
     * This does not activate shipping costs within the shopping cart
     * This is an estimated cost, and needs to be stored in the cart
     *  in order to avoid calculating possible shipping options on every page
     *
     * @param Shipment $shipment
     * @return $this
     */
    public function addShippingMethod(Shipment $shipment)
    {
        $this->initCart();
        $this->getCart()->addShippingMethod($shipment);
        return $this;
    }

    /**
     * @return array
     */
    public function getShippingMethods()
    {
        $this->initCart();
        return $this->getCart()->getShippingMethods();
    }

    /**
     * Empty both shipments and shipment options
     *
     * @return $this
     */
    public function removeShipments()
    {
        $this->initCart()->getCart()->unsetShipments();
        return $this;
    }

    /**
     * @return $this
     */
    public function removeShippingMethods()
    {
        $this->initCart()->getCart()->unsetShippingMethods();
        return $this;
    }

    /**
     * @param string|int $key
     * @param bool $isKey
     * @return $this
     */
    public function removeShipment($key, $isKey = true)
    {
        $this->initCart()->getCart()->unsetShipment($key, $isKey);
        return $this;
    }

    /**
     * @return $this
     */
    public function collectTotals()
    {
        $this->initCart();
        $totals = $this->getCartTotalService()
            ->setIsShippingEnabled($this->getShippingService()->getIsShippingEnabled())
            ->setIsTaxEnabled($this->getTaxService()->getIsTaxEnabled())
            ->setCart($this->getCart())
            ->collectTotals()
            ->getTotals();

        $this->getCart()->setTotals($totals); // for saving state
        return $this;
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function setTotals(array $totals)
    {
        $this->initCart();
        $this->getCart()->setTotals($totals); // for saving state
        return $this;
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        $this->initCart();
        return $this->getCart()->getTotals();
    }

    /**
     * @return $this
     */
    public function collectShippingMethods()
    {
        if (!$this->getShippingService()->getIsShippingEnabled()) {
            return $this;
        }

        $customer = $this->getCustomer();

        // todo : in the future, use ConditionCompare

        $request = new RateRequest();
        $request->fromArray([
            'to_array'    => 0,
            'include_all' => 0,
            'postcode'    => $customer->getShippingPostcode(),
            'country_id'  => $customer->getShippingCountryId(),
            'region'      => $customer->getShippingRegion(),
        ]);

        $rates = $this->getShippingService()->collectShippingRates($request);
        $this->setRates($rates);

        $shipments = $this->getCart()->getShipments();
        $shipment = isset($shipments[0])
            ? $shipments[0]
            : false;

        $code = ($shipment && $shipment->getCode())
            ? $shipment->getCode()
            : '';

        if ($code && !$this->getCart()->hasShippingMethodCode($code)) {
            $this->removeShipments();
        }

        if (!$this->getCart()->hasShipments()
            && $this->getCart()->hasShippingMethods()
            && $this->getCart()->hasItems()) {

            $methods = $this->getCart()->getShippingMethods();
            if ($methods) {
                $this->addShipment($methods[0]);
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
        $this->initCart();
        $this->getCart()->setPaymentMethodCodes($methodCodes);
        return $this;
    }
}
