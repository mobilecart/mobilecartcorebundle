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

/**
 * Class CartService
 * @package MobileCart\CoreBundle\Service
 *
 * This class provides a Singleton Shopping Cart as a Service
 */
class CartService
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    protected $shippingService;

    /**
     * @var \MobileCart\CoreBundle\Service\DiscountService
     */
    protected $discountService;

    /**
     * @var \MobileCart\CoreBundle\Service\TaxService
     */
    protected $taxService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartTotalService
     */
    protected $cartTotalService;

    /**
     * @var \MobileCart\CoreBundle\Service\GeographyService
     */
    protected $geographyService;

    /**
     * @var array
     */
    protected $allowedCountryIds = [];

    /**
     * @var bool
     */
    protected $allowGuestCheckout = false;

    /**
     * Single page (1) or multi page form (0)
     *
     * @var int
     */
    protected $isSpaEnabled = 1;

    /**
     * @param \MobileCart\CoreBundle\Service\CartTotalService $cartTotalService
     * @return $this
     */
    public function setCartTotalService($cartTotalService)
    {
        $this->cartTotalService = $cartTotalService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->cartTotalService;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\ShippingService $shippingService
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
     * @param \MobileCart\CoreBundle\Service\DiscountService $discountService
     * @return $this
     */
    public function setDiscountService($discountService)
    {
        $this->discountService = $discountService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DiscountService
     */
    public function getDiscountService()
    {
        return $this->discountService;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\TaxService $taxService
     * @return $this
     */
    public function setTaxService($taxService)
    {
        $this->taxService = $taxService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\TaxService
     */
    public function getTaxService()
    {
        return $this->taxService;
    }

    /**
     * @return Cart
     */
    public function getCartInstance()
    {
        return new Cart();
    }

    /**
     * @return Customer
     */
    public function getCustomerInstance()
    {
        return new Customer();
    }

    /**
     * @return Item
     */
    public function getItemInstance()
    {
        return new Item();
    }

    /**
     * @return Discount
     */
    public function getDiscountInstance()
    {
        return new Discount();
    }

    /**
     * @return Shipment
     */
    public function getShipmentInstance()
    {
        return new Shipment();
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
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
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
        $this->removeShipments();
        return $this;
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
        return $this->initCart()->getCart()->getProductIds();
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function removeProductId($productId)
    {
        $this->initCart()->getCart()->removeProductId($productId);
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
        $this->initCart()->getCart()->setProductQty($productId, $qty);
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
        $this->initCart()->getCart()->addProductQty($productId, $qty);
        return $this;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->initCart()->getCart()->setCustomer($customer);
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->initCart()->getCart()->getCustomer();
    }

    /**
     * @param $entity
     * @return $this
     */
    public function setCustomerEntity($entity)
    {
        $customer = new Customer();
        $customer->fromArray($entity->getBaseData());
        return $this->setCustomer($customer);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        $customer = $this->initCart()->getCart()->getCustomer();
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
        $this->initCart()->getCart()->addShipment($shipment);
        return $this;
    }

    /**
     * @return string
     */
    public function getShipmentMethod()
    {
        $shipments = $this->initCart()->getCart()->getShipments();
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
        $this->initCart()->getCart()->unsetShippingMethods();
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
        $this->initCart()->getCart()->addShippingMethod($shipment);
        return $this;
    }

    /**
     * @return array
     */
    public function getShippingMethods()
    {
        return $this->initCart()->getCart()->getShippingMethods();
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
            // ->setIsDiscountEnabled() // todo: wire this up
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
     * @param $key
     * @return bool
     */
    public function getTotal($key)
    {
        $this->initCart();
        return $this->getCart()->getTotal($key);
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

        // todo : use ConditionCompare

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

    /**
     * @param \MobileCart\CoreBundle\Service\GeographyService $geographyService
     * @return $this
     */
    public function setGeographyService($geographyService)
    {
        $this->geographyService = $geographyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\GeographyService
     */
    public function getGeographyService()
    {
        return $this->geographyService;
    }

    /**
     * @param $countryIdsStr
     * @return $this
     */
    public function setAllowedCountryIds($countryIdsStr)
    {
        $countryIds = explode(',', $countryIdsStr);
        $this->allowedCountryIds = array_map('trim', $countryIds);
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedCountryIds()
    {
        return $this->allowedCountryIds;
    }

    /**
     * @param $countryId
     * @return bool
     */
    public function isAllowedCountryId($countryId)
    {
        return in_array($countryId, $this->allowedCountryIds);
    }

    /**
     * @return array
     */
    public function getCountryRegions()
    {
        return $this->getGeographyService()->getRegionsByCountries($this->getAllowedCountryIds());
    }

    /**
     * @param bool $yesNo
     * @return $this
     */
    public function setAllowGuestCheckout($yesNo)
    {
        $this->allowGuestCheckout = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowGuestCheckout()
    {
        return $this->allowGuestCheckout;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsSpaEnabled($isEnabled)
    {
        $this->isSpaEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsSpaEnabled()
    {
        return $this->isSpaEnabled;
    }
}
