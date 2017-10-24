<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\CartComponent;

/**
 * Class Cart
 * @package MobileCart\CoreBundle\CartComponent
 */
class Cart extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    const ID = 'id';
    const CURRENCY = 'currency';
    const CUSTOMER = 'customer';
    const DISCOUNTS = 'discounts';
    const ITEMS = 'items';
    const SHIPMENTS = 'shipments';
    const SHIPPING_METHODS = 'shipping_methods';
    const TOTALS = 'totals';
    const INCLUDE_TAX = 'include_tax';
    const TAX_RATE = 'tax_rate';
    const PRECISION = 'precision';
    const CALCULATOR_PRECISION = 'calculator_precision';
    const DISCOUNT_TAXABLE_LAST = 'discount_taxable_last';

    public function __construct()
    {
        parent::__construct($this->getDefaults());
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            self::ID => 0,
            self::CURRENCY => 'USD',
            self::CUSTOMER => new Customer(),
            self::ITEMS => [],
            self::DISCOUNTS => [],
            self::SHIPMENTS => [],
            self::SHIPPING_METHODS => [],
            self::INCLUDE_TAX => false,
            self::TAX_RATE => 0.0,
            self::PRECISION => 2,
            self::CALCULATOR_PRECISION => 4,
            self::DISCOUNT_TAXABLE_LAST => true,
        ];
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->data[self::ID] = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->data[self::ID];
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->data[self::CURRENCY] = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->data[self::CURRENCY];
    }

    /**
     * @param bool $includeTax
     * @return $this
     */
    public function setIncludeTax($includeTax)
    {
        $this->data[self::INCLUDE_TAX] = (bool) $includeTax;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIncludeTax()
    {
        return (bool) $this->data[self::INCLUDE_TAX];
    }

    /**
     * @param float $taxRate
     * @return $this
     */
    public function setTaxRate($taxRate)
    {
        $this->data[self::TAX_RATE] = (float) $taxRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return (float) $this->data[self::TAX_RATE];
    }

    /**
     * @param bool $discountTaxableLast
     * @return $this
     */
    public function setDiscountTaxableLast($discountTaxableLast)
    {
        $this->data[self::DISCOUNT_TAXABLE_LAST] = (bool) $discountTaxableLast;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDiscountTaxableLast()
    {
        return (bool) $this->data[self::DISCOUNT_TAXABLE_LAST];
    }

    /**
     * @param $key
     * @return bool
     */
    public function getTotal($key)
    {
        if (!$this->getTotals()) {
            return false;
        }

        foreach($this->getTotals() as $total) {
            if ($total->getKey() == $key) {
                return $total;
            }
        }

        return false;
    }

    /**
     * @return $this
     */
    protected function initTotals()
    {
        if (!isset($this->data[self::TOTALS]) || !is_array($this->data[self::TOTALS])) {
            $this->data[self::TOTALS] = [];
        }

        return $this;
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function setTotals(array $totals)
    {
        $this->data[self::TOTALS] = $totals;
        return $this;
    }

    /**
     * @return Total[]
     */
    public function getTotals()
    {
        $this->initTotals();
        return $this->data[self::TOTALS];
    }

    /**
     * Retrieve calculator with current Cart instance
     *
     * @return Calculator
     */
    public function getCalculator()
    {
        return new Calculator($this);
    }

    /**
     * Calculate and retrieve current cart totals from calculator
     *  NOTE: This does not use the Event system,
     *   which is generally needed within the web app
     *
     * @return array
     */
    public function calculateTotals()
    {
        return $this->getCalculator()->getTotals();
    }

    /**
     * Get item/shipment discounts from calculator
     *
     * @return array
     */
    public function getDiscountGrid()
    {
        return $this->getCalculator()->getDiscountGrid();
    }

    /**
     * Get current discounted cart totals from calculator
     *
     * @return array
     */
    public function getDiscountedTotals()
    {
        return $this->getCalculator()->getDiscountedTotals();
    }

    /**
     * Enable string casting for this class
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @return Item
     */
    public function createItem()
    {
        return new Item();
    }

    /**
     * @return $this
     */
    protected function initItems()
    {
        if (!isset($this->data[self::ITEMS]) || !is_array($this->data[self::ITEMS])) {
            $this->data[self::ITEMS] = [];
        }

        return $this;
    }

    /**
     * @param Item $item
     * @return $this
     */
    public function addItem(Item $item)
    {
        $this->initItems();
        $this->data[self::ITEMS][] = $item;
        return $this;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        $this->initItems();
        return $this->data[self::ITEMS];
    }

    /**
     * @return bool
     */
    public function hasItems()
    {
        $this->initItems();
        return count($this->data[self::ITEMS]) > 0;
    }

    /**
     * @return Shipment
     */
    public function createShipment()
    {
        return new Shipment();
    }

    /**
     * @return $this
     */
    public function initShipments()
    {
        if (!isset($this->data[self::SHIPMENTS]) || !is_array($this->data[self::SHIPMENTS])) {
            $this->data[self::SHIPMENTS] = [];
        }

        return $this;
    }

    /**
     * @param Shipment $shipment
     * @return $this
     */
    public function addShipment(Shipment $shipment)
    {
        $this->initShipments();
        $this->data[self::SHIPMENTS][] = $shipment;
        return $this;
    }

    /**
     * @return Shipment[]
     */
    public function getShipments()
    {
        $this->initShipments();
        return $this->data[self::SHIPMENTS];
    }

    /**
     * @return bool
     */
    public function hasShipments()
    {
        $this->initShipments();
        return count($this->data[self::SHIPMENTS]) > 0;
    }

    /**
     * @return $this
     */
    protected function initDiscounts()
    {
        if (!isset($this->data[self::DISCOUNTS]) || !is_array($this->data[self::DISCOUNTS])) {
            $this->data[self::DISCOUNTS] = [];
        }

        return $this;
    }

    /**
     * @return Discount
     */
    public function createDiscount()
    {
        return new Discount();
    }

    /**
     * @return Customer
     */
    public function createCustomer()
    {
        return new Customer();
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->data[self::CUSTOMER] = $customer;
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->data[self::CUSTOMER];
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        $customer = $this->getCustomer();
        if ($customer instanceof Customer) {
            return $customer->getId();
        }

        return 0;
    }

    /**
     * Export cart discounts as an associative array
     *
     * @return array of Discounts
     */
    public function getDiscountsData()
    {
        $discounts = [];
        if ($this->hasDiscounts()) {
            foreach($this->getDiscounts() as $discount) {
                $discounts[] = $discount->toArray();
            }
        }
        return $discounts;
    }

    /**
     * Export cart items as an associative array
     *
     * @return array of Items
     */
    public function getItemsData()
    {
        $items = [];
        if ($this->hasItems()) {
            foreach($this->getItems() as $item) {
                $items[] = $item->toArray();
            }
        }
        return $items;
    }

    /**
     * Export cart shipments as an associative array
     *
     * @return array of Shipments
     */
    public function getShipmentsData()
    {
        $shipments = [];
        if ($this->hasShipments()) {
            foreach($this->getShipments() as $shipment) {
                $shipments[] = $shipment->toArray();
            }
        }
        return $shipments;
    }

    /**
     * Export cart shipments as an associative array
     *
     * @return array of Shipments
     */
    public function getShippingMethodsData()
    {
        $shipments = [];
        if ($this->hasShippingMethods()) {
            foreach($this->getShippingMethods() as $srcAddressKey => $addressIds) {
                if (!$addressIds) {
                    continue;
                }
                $shipments[$srcAddressKey] = [];
                foreach($addressIds as $addressId => $methods) {
                    if (!$methods) {
                        continue;
                    }
                    $shipments[$srcAddressKey][$addressId] = [];
                    foreach($methods as $method) {
                        $shipments[$srcAddressKey][$addressId][] = $method->toArray();
                    }
                }
            }
        }

        return $shipments;
    }
    
    /**
     * Import object data from json string.
     * Note: Watch out for single index arrays becoming stdClass objects
     *
     * @param string $json
     * @param bool
     * @return Cart
     */
    public function importJson($json, $reset = true)
    {
        // strict parameter
        if (!is_string($json)) {
            return false;
        }
        
        $data = @ (array) json_decode($json);
        return $this->fromArray($data, $reset);
    }

    /**
     * @param array $cart
     * @return $this
     */
    public function fromArray(array $cart)
    {
        if (isset($cart[self::ID])) {
            $this->setId($cart[self::ID]);
        }

        if (isset($cart[self::CUSTOMER])) {
            $customerObj = $cart[self::CUSTOMER];
            $customerData = ($customerObj instanceof \stdClass)
                ? get_object_vars($customerObj)
                : (array) $customerObj;

            foreach($customerData as $k => $v) {
                if ($v instanceof \stdClass) {
                    $customerData[$k] = new ArrayWrapper(get_object_vars($v));
                }
            }

            $customer = new Customer();
            $customer->fromArray($customerData);
            $this->setCustomer($customer);
        }

        if (isset($cart[self::ITEMS]) && count($cart[self::ITEMS]) > 0) {
            $items = $cart[self::ITEMS];
            foreach($items as $itemObj) {

                $itemData = ($itemObj instanceof \stdClass)
                    ? get_object_vars($itemObj)
                    : (array) $itemObj;

                foreach($itemData as $k => $v) {
                    if (is_object($v)) {
                        $itemData[$k] = new ArrayWrapper(get_object_vars($v));
                    }
                }

                $item = new Item();
                $item->fromArray($itemData);
                $this->addItem($item);
            }
        }

        if (isset($cart[self::SHIPMENTS]) && count($cart[self::SHIPMENTS]) > 0) {
            $shipments = $cart[self::SHIPMENTS];
            foreach($shipments as $shipmentObj) {

                $shipmentData = ($shipmentObj instanceof \stdClass)
                    ? get_object_vars($shipmentObj)
                    : (array) $shipmentObj;

                $shipment = new Shipment();
                $shipment->fromArray($shipmentData);
                $this->addShipment($shipment);
            }
        }

        //should not be able to save/import shipment method quotes

        if (isset($cart[self::DISCOUNTS]) && count($cart[self::DISCOUNTS]) > 0) {
            $discounts = $cart[self::DISCOUNTS];
            foreach($discounts as $discountObj) {

                $discountData = ($discountObj instanceof \stdClass)
                    ? get_object_vars($discountObj)
                    : (array) $discountObj;

                $discount = new Discount();
                $discount->fromArray($discountData);
                $this->addDiscount($discount);
            }
        }

        if (isset($cart[self::INCLUDE_TAX])) {
            $includeTax = $cart[self::INCLUDE_TAX];
            $this->setIncludeTax($includeTax);
        }

        if (isset($cart[self::TAX_RATE])) {
            $taxRate = $cart[self::TAX_RATE];
            $this->setTaxRate($taxRate);
        }

        if (isset($cart[self::DISCOUNT_TAXABLE_LAST])) {
            $discountTaxableLast = $cart[self::DISCOUNT_TAXABLE_LAST];
            $this->setDiscountTaxableLast($discountTaxableLast);
        }

        if ($cart) {
            foreach($cart as $k => $v) {
                if (in_array($k, [
                    self::ID,
                    self::CUSTOMER,
                    self::ITEMS,
                    self::SHIPMENTS,
                    self::SHIPPING_METHODS,
                    self::DISCOUNTS,
                    self::INCLUDE_TAX,
                    self::TAX_RATE,
                    self::DISCOUNT_TAXABLE_LAST,
                ])) {
                    continue;
                }

                if ($v instanceof \stdClass) {
                    $cart[$k] = new ArrayWrapper(get_object_vars($v));
                } elseif (is_array($v)) {
                    $cart[$k] = new ArrayWrapper($v);
                } elseif (is_scalar($v)) {
                    $cart[$k] = $v;
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function reapplyDiscounts()
    {
        if ($this->hasDiscounts()) {
            foreach($this->getDiscounts() as $discount) {
                $discount->reapplyIfValid($this);
            }
        }

        return $this;
    }

    /**
     * Validate a RuleCondition against this Cart instance
     *
     * @param RuleCondition
     * @return bool
     */
    public function isValidCondition(RuleCondition $condition)
    {
        switch($condition->getEntityField()) {
            case 'base_total':
                $condition->setSourceValue($this->getCalculator()->getGrandTotal());
                break;
            case 'base_item_total':
                $condition->setSourceValue($this->getCalculator()->getItemTotal());
                break;
            case 'base_shipment_total':
                $condition->setSourceValue($this->getCalculator()->getShipmentTotal());
                break;
            case 'discounted_item_total':
                $condition->setSourceValue($this->getCalculator()->getDiscountedItemTotal());
                break;
            case 'discounted_shipment_total':
                $condition->setSourceValue($this->getCalculator()->getDiscountedShipmentTotal());
                break;
            default:
                $condition->setSourceValue($this->get($condition->getEntityField()));
                break;
        }

        return $condition->isValid();
    }

    /**
     * Check if this item validates a tree of conditions
     *
     * @param RuleConditionCompare
     * @return bool
     */
    public function isValidConditionCompare(RuleConditionCompare $compare)
    {
        return $compare->isValid($this);
    }

    /**
     * @param $key
     * @param $value
     * @return int|null|string
     */
    public function findItemIdx($key, $value)
    {
        if (!$this->hasItems()) {
            return null;
        }

        foreach($this->getItems() as $idx => $item) {
            if ($item->get($key) == $value) {
                return $idx;
            }
        }

        return null;
    }

    /**
     * @param $idx
     * @return Item|null
     */
    public function getItem($idx)
    {
        return isset($this->data[self::ITEMS][$idx])
            ? $this->data[self::ITEMS][$idx]
            : null;
    }

    /**
     * @param $key
     * @param $value
     * @return bool|null
     */
    public function findItem($key, $value)
    {
        $idx = $this->findItemIdx($key, $value);
        return is_numeric($idx)
            ? $this->getItem($idx)
            : null;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function setItems(array $items = [])
    {
        $this->data[self::ITEMS] = array_values($items); // the array keys just make a mess in the json conversion
        return $this;
    }

    /**
     * @param $idx
     * @return $this
     */
    public function unsetItem($idx)
    {
        if (isset($this->data[self::ITEMS]) && isset($this->data[self::ITEMS][$idx])) {
            unset($this->data[self::ITEMS][$idx]);
        }

        $this->setItems($this->getItems()); // array key handling for json encoding
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @param $addressId
     * @param $srcAddressKey
     * @return int|null|string
     */
    public function findShipmentIdx($key, $value, $addressId='main', $srcAddressKey='main')
    {
        if (!$this->hasShipments()) {
            return null;
        }

        // note: the customer_address_id is either 'main' or an integer
        //  it is not prefiex like in shipping_methods
        foreach($this->getShipments() as $idx => $shipment) {
            if ($shipment->get($key) == $value
                && $shipment->get('customer_address_id') == $addressId
                && $shipment->get('source_address_key') == $srcAddressKey
            ) {
                return $idx;
            }
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     * @param $addressId
     * @param $srcAddressKey
     * @return bool|null
     */
    public function findShipment($key, $value, $addressId='main', $srcAddressKey='main')
    {
        $idx = $this->findShipmentIdx($key, $value, $addressId, $srcAddressKey);
        return is_numeric($idx)
            ? $this->getShipment($idx)
            : null;
    }

    /**
     * @param $method
     * @param string|int $addressId
     * @param string $srcAddressKey
     * @return $this
     */
    public function addShippingMethod($method, $addressId='main', $srcAddressKey='main')
    {
        if (!isset($this->data['shipping_methods'])
            || !is_array($this->data['shipping_methods'])
        ) {
            $this->data['shipping_methods'] = [];
        }

        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if (!isset($this->data['shipping_methods'][$srcAddressKey])
            || !is_array($this->data['shipping_methods'][$srcAddressKey])
        ) {
            $this->data['shipping_methods'][$srcAddressKey] = [];
        }

        if (!isset($this->data['shipping_methods'][$srcAddressKey][$addressId])
            || !is_array($this->data['shipping_methods'][$srcAddressKey][$addressId])
        ) {
            $this->data['shipping_methods'][$srcAddressKey][$addressId] = [];
        }

        $this->data['shipping_methods'][$srcAddressKey][$addressId][] = $method;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllShippingMethods()
    {
        if (!isset($this->data['shipping_methods'])
            || !is_array($this->data['shipping_methods'])
        ) {
            return [];
        }

        return $this->data['shipping_methods'];
    }

    /**
     * @param string $addressId
     * @param string $srcAddressKey
     * @return array
     */
    public function getShippingMethods($addressId='main', $srcAddressKey='main')
    {
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if (!isset($this->data['shipping_methods'][$srcAddressKey])
            || !is_array($this->data['shipping_methods'][$srcAddressKey])
        ) {
            return [];
        }

        if (!isset($this->data['shipping_methods'][$srcAddressKey][$addressId])
            || !is_array($this->data['shipping_methods'][$srcAddressKey][$addressId])
        ) {
            return [];
        }

        return $this->data['shipping_methods'][$srcAddressKey][$addressId];
    }

    /**
     * @param $key
     * @param $value
     * @param $addressId
     * @param $srcAddressKey
     * @return int|null|string
     */
    public function findShippingMethodIdx($key, $value, $addressId='main', $srcAddressKey='main')
    {
        if (!$this->hasShippingMethods()) {
            return null;
        }

        // we prefix the integers so that we have a better string for a key
        //  this is only in shipping_methods, not in shipments
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if ($methods = $this->getShippingMethods($addressId, $srcAddressKey)) {
            foreach($methods as $idx => $shipment) {
                if ($shipment->get($key) == $value) {
                    return $idx;
                }
            }
        }

        return null;
    }

    /**
     * @param $key
     * @param $value
     * @param $addressId
     * @param $srcAddressKey
     * @return bool|null
     */
    public function findShippingMethod($key, $value, $addressId='main', $srcAddressKey='main')
    {
        $idx = $this->findShippingMethodIdx($key, $value, $addressId, $srcAddressKey);
        return is_numeric($idx)
            ? $this->getShippingMethod($idx, $addressId, $srcAddressKey)
            : null;
    }

    /**
     * @param $key
     * @param $value
     * @return int|null|string
     */
    public function findDiscountIdx($key, $value)
    {
        if (!$this->hasDiscounts()) {
            return null;
        }

        foreach($this->getDiscounts() as $idx => $item) {
            if ($item->get($key) == $value) {
                return $idx;
            }
        }

        return null;
    }

    /**
     * @param Discount $discount
     * @return $this
     */
    public function addDiscount(Discount $discount)
    {
        $this->initDiscounts();
        $this->data[self::DISCOUNTS][] = $discount;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return bool|null
     */
    public function findDiscount($key, $value)
    {
        $idx = $this->findDiscountIdx($key, $value);
        return is_numeric($idx)
            ? $this->getDiscount($idx)
            : null;
    }

    /**
     * @param $id
     * @return $this
     */
    public function unsetItemById($id)
    {
        $idx = $this->findItemIdx('id', $id);
        if (is_numeric($idx)) {
            $this->unsetItem($idx);
        }
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @return $this
     */
    public function unsetItems()
    {
        $this->setItems([]);
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $sku
     * @return $this
     */
    public function unsetItemBySku($sku)
    {
        $idx = $this->findItemIdx('sku', $sku);
        if (is_numeric($idx)) {
            $this->unsetItem($idx);
        }
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function unsetItemByProductId($productId)
    {
        $idx = $this->findItemIdx('product_id', $productId);
        if (is_numeric($idx)) {
            $this->unsetItem($idx);
        }
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasItem($key)
    {
        return isset($this->data[self::ITEMS][$key]);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasItemId($id)
    {
        return is_numeric($this->findItemIdx('id', $id));
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasProductId($id)
    {
        return is_numeric($this->findItemIdx('product_id', $id));
    }

    /**
     * @param $sku
     * @return bool|null
     */
    public function getItemBySku($sku)
    {
        $idx = $this->findItemIdx('sku', $sku);
        if (!is_numeric($idx)) {
            return false;
        }
        return $this->getItem($idx);
    }

    /**
     * @param $productId
     * @return Item|null
     */
    public function getItemByProductId($productId)
    {
        $idx = $this->findItemIdx('product_id', $productId);
        if (!is_numeric($idx)) {
            return null;
        }
        return $this->getItem($idx);
    }

    /**
     * @param $sku
     * @return bool
     */
    public function hasSku($sku)
    {
        return is_numeric($this->findItemIdx('sku', $sku));
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function removeProductId($productId)
    {
        $idx = $this->findItemIdx('product_id', $productId);
        if (is_numeric($idx)) {
            $this->unsetItem($idx);
        }
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $itemId
     * @return $this
     */
    public function removeItemId($itemId)
    {
        $idx = $this->findItemIdx('id', $itemId);
        if (is_numeric($idx)) {
            $this->unsetItem($idx);
        }
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function setProductQty($productId, $qty)
    {
        if ($this->hasProductId($productId)) {
            $idx = $this->findItemIdx('product_id', $productId);
            $this->data[self::ITEMS][$idx]->setQty($qty);
        }
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function addProductQty($productId, $qty)
    {
        if ($this->hasProductId($productId)) {
            $idx = $this->findItemIdx('product_id', $productId);
            $item = $this->getItemByProductId($productId);
            $qty += $item->getQty();
            $this->data[self::ITEMS][$idx]->setQty($qty);
        }
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        $productIds = [];
        if ($this->hasItems()) {
            foreach($this->getItems() as $item) {
                $productIds[] = $item->get('product_id');
            }
        }
        return $productIds;
    }

    /**
     * Get keys of cart items that have been specified in discounts
     * This helps with separating specific discounts
     *
     * @return array
     */
    public function getSpecifiedDiscountItemKeys()
    {
        $keys = [];
        if (count($this->getDiscounts()) > 0) {
            foreach($this->getDiscounts() as $discount) {

                if ($discount->getAppliedTo() != Discount::APPLIED_TO_SPECIFIED) {
                    continue;
                }

                if (count($discount->getItems()) > 0) {
                    foreach($discount->getItems() as $itemKey => $qty) {
                        $keys[$itemKey] = $itemKey;
                    }
                }
            }
        }
        return $keys;
    }

    /**
     * @return Discount[]
     */
    public function getDiscounts()
    {
        $this->initDiscounts();
        return $this->data[self::DISCOUNTS];
    }

    /**
     * @return bool
     */
    public function hasDiscounts()
    {
        return count($this->getDiscounts()) > 0;
    }

    /**
     * @param $key
     * @return bool
     */
    public function getDiscount($key)
    {
        return isset($this->data[self::DISCOUNTS][$key])
            ? $this->data[self::DISCOUNTS][$key]
            : false;
    }

    /**
     * @param $key
     * @param Discount $discount
     * @return $this
     */
    public function setDiscount($key, Discount $discount)
    {
        $this->data[self::DISCOUNTS][$key] = $discount;
        return $this;
    }

    /**
     * @param Discount $discount
     * @return $this|mixed
     */
    public function removeDiscount(Discount $discount)
    {
        if ($discount->getId()) {
            return $this->removeDiscountId($discount->getId());
        }
        return $this;
    }

    /**
     * @param $discountId
     * @return mixed
     */
    public function removeDiscountId($discountId)
    {
        $idx = $this->findDiscountIdx('id', $discountId);
        if (is_numeric($idx)) {
            unset($this->data[self::DISCOUNTS][$idx]);
        }
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetDiscount($key)
    {
        if (isset($this->data[self::DISCOUNTS][$key])) {
            unset($this->data[self::DISCOUNTS][$key]);
        }

        return $this;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function hasDiscountId($id)
    {
        return is_numeric($this->findDiscountIdx('id', $id));
    }

    /**
     * @param string $code
     * @return bool
     */
    public function hasDiscountCouponCode($code)
    {
        return is_numeric($this->findDiscountIdx('coupon_code', $code));
    }

    /**
     * @param $key
     * @return bool
     */
    public function getShipment($key)
    {
        return isset($this->data[self::SHIPMENTS][$key])
            ? $this->data[self::SHIPMENTS][$key]
            : null;
    }

    /**
     * @param $key
     * @param $addressId
     * @param $srcAddressKey
     * @return \MobileCart\CoreBundle\Shipping\Rate|null
     */
    public function getShippingMethod($key, $addressId='main', $srcAddressKey='main')
    {
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if (!isset($this->data['shipping_methods'][$srcAddressKey])
            || !is_array($this->data['shipping_methods'][$srcAddressKey])
        ) {
            return null;
        }

        if (!isset($this->data['shipping_methods'][$srcAddressKey][$addressId])
            || !is_array($this->data['shipping_methods'][$srcAddressKey][$addressId])
        ) {
            return null;
        }

        return isset($this->data['shipping_methods'][$srcAddressKey][$addressId][$key])
            ? $this->data['shipping_methods'][$srcAddressKey][$addressId][$key]
            : null;
    }

    /**
     * Update / Replace Shipment
     *
     * @param $idx
     * @param Shipment $shipment
     * @return $this
     */
    public function setShipment($idx, Shipment $shipment)
    {
        $this->data[self::SHIPMENTS][$idx] = $shipment;
        // being tedious with json structure here
        $this->data[self::SHIPMENTS] = array_values($this->data[self::SHIPMENTS]);
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param array $shipments
     * @return $this
     */
    public function setShipments(array $shipments = [])
    {
        $this->data[self::SHIPMENTS] = array_values($shipments);
        return $this;
    }

    /**
     * @param $id
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShipmentById($id, $addressId='main', $srcAddressKey='main')
    {
        $idx = $this->findShipmentIdx('id', $id, $addressId, $srcAddressKey);
        if (is_numeric($idx)
            && isset($this->data[self::SHIPMENTS][$idx])) {

            unset($this->data[self::SHIPMENTS][$idx]);
        }
        $this->data[self::SHIPMENTS] = array_values($this->data[self::SHIPMENTS]); // strip keys for json structure
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $code
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShipmentByCode($code, $addressId='main', $srcAddressKey='main')
    {
        $idx = $this->findShipmentIdx('code', $code, $addressId, $srcAddressKey);
        if (is_numeric($idx)
            && isset($this->data[self::SHIPMENTS][$idx])) {

            unset($this->data[self::SHIPMENTS][$idx]);
        }
        $this->data[self::SHIPMENTS] = array_values($this->data[self::SHIPMENTS]); // strip keys for json structure
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetShipment($key)
    {
        if (isset($this->data[self::SHIPMENTS][$key])) {
            unset($this->data[self::SHIPMENTS][$key]);
        }
        $this->data[self::SHIPMENTS] = array_values($this->data[self::SHIPMENTS]); // strip keys for json structure
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $key
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function unsetShippingMethod($key, $addressId='main', $srcAddressKey='main')
    {
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if (isset($this->data['shipping_methods'][$srcAddressKey][$addressId][$key])) {
            unset($this->data['shipping_methods'][$srcAddressKey][$addressId][$key]);
        }

        // dont need to remove anything from discounts
        $this->data[self::SHIPMENTS][$srcAddressKey][$addressId] = array_values($this->data[self::SHIPMENTS][$srcAddressKey][$addressId]); // strip keys for json structure

        return $this;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return Shipment|null
     */
    public function getAddressShipment($addressId, $srcAddressKey='main')
    {
        if ($shipments = $this->getShipments()) {
            foreach($shipments as $shipment) {
                if ($shipment->get('customer_address_id') == $addressId
                    && $shipment->get('source_address_key') == $srcAddressKey
                ) {
                    return $shipment;
                }
            }
        }

        return null;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function addressHasShipment($addressId, $srcAddressKey='main')
    {
        if ($shipments = $this->getShipments()) {
            foreach($shipments as $shipment) {
                if ($shipment->get('customer_address_id') == $addressId
                    && $shipment->get('source_address_key') == $srcAddressKey
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function unsetShipments($addressId='', $srcAddressKey='main')
    {
        if ($addressId) {
            if ($this->hasShipments()) {
                foreach($this->getShipments() as $idx => $shipment) {
                    if ($shipment->get('customer_address_id') == $addressId
                        && $shipment->get('source_address_key') == $srcAddressKey
                    ) {
                        unset($this->data[self::SHIPMENTS][$idx]);
                    }
                }

                $this->data[self::SHIPMENTS] = array_values($this->data[self::SHIPMENTS]); // strip keys for json structure
            }
        } else {
            $this->setShipments([]);
        }

        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function unsetShippingMethods($addressId='', $srcAddressKey='main')
    {
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId;
        }

        if ($addressId) {
            if ($this->getAllShippingMethods()) {
                foreach($this->getAllShippingMethods() as $aSrcAddressKey => $addressIds) {

                    if (!$addressIds
                        || $srcAddressKey != $aSrcAddressKey
                    ) {
                        continue;
                    }

                    foreach($addressIds as $anAddressId => $methods) {

                        // if there's nothing to do, continue
                        if (!$methods
                            || $srcAddressKey != $aSrcAddressKey
                            || $addressId != $anAddressId
                            || !isset($this->data['shipping_methods'][$aSrcAddressKey][$anAddressId])
                        ) {

                            continue;
                        }

                        $this->data['shipping_methods'][$aSrcAddressKey][$addressId] = [];
                    }
                }
            }
        } else {
            $this->data['shipping_methods'] = [];
        }

        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $id
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function hasShipmentMethodId($id, $addressId='main', $srcAddressKey='main')
    {
        return is_numeric($this->findShipmentIdx('id', $id, $addressId, $srcAddressKey));
    }

    /**
     * Find Shipment added to the cart
     *
     * @param $code
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function hasShipmentMethodCode($code, $addressId='main', $srcAddressKey='main')
    {
        if ($addressId != 'main' && !is_numeric($addressId)) {
            $addressId = (int) str_replace('address_', '', $addressId);
        }

        return is_numeric($this->findShipmentIdx('code', $code, $addressId, $srcAddressKey));
    }

    /**
     * Find Shipping Method which might get added to the Cart as a Shipment
     *
     * @param $code
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function hasShippingMethodCode($code, $addressId='main', $srcAddressKey='main')
    {
        return is_numeric($this->findShippingMethodIdx('code', $code, $addressId, $srcAddressKey));
    }

    /**
     * @param $id
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function hasShippingMethodId($id, $addressId='main', $srcAddressKey='main')
    {
        return is_numeric($this->findShippingMethodIdx('id', $id, $addressId, $srcAddressKey));
    }

    /**
     * @param string $addressId
     * @return string
     */
    public function addressLabel($addressId='main')
    {
        if ($addressId == 'main') {

            $customer = $this->getCustomer();
            if (strlen(trim($customer->getShippingStreet())) > 3) {
                $label = "{$customer->getShippingStreet()} {$customer->getShippingCity()}, {$customer->getShippingRegion()}";
                return $label;
            }

            return 'Main Address';
        } else {

            if (!is_numeric($addressId)) {
                $addressId = str_replace('address_', '', $addressId);
            }

            $addressId = (int) $addressId;

            if ($addressId && $this->getCustomer()->getAddresses()) {
                foreach($this->getCustomer()->getAddresses() as $address) {

                    if ($address instanceof \stdClass) {
                        $address = get_object_vars($address);
                    }

                    if (is_array($address)) {
                        $address = new ArrayWrapper($address);
                    }

                    if ($addressId == $address->getId()
                        && strlen(trim($address->getStreet())) > 3
                    ) {
                        $label = "{$address->getStreet()} {$address->getCity()}, {$address->getRegion()}";
                        return $label;
                    }
                }
            }

            return 'Address';
        }
    }

    /**
     * Get keys of shipments that have been specified in discounts
     *
     * @return array
     */
    public function getSpecifiedDiscountShipmentKeys()
    {
        $keys = [];
        if ($this->hasDiscounts()) {
            foreach($this->getDiscounts() as $key => $discount) {
                if ($discount->getTo() != Discount::APPLIED_TO_SPECIFIED) {
                    continue;
                }

                if (count($discount->getShipments()) > 0) {
                    foreach($discount->getShipments() as $shipmentKey => $value) {
                        $keys[$shipmentKey] = $shipmentKey;
                    }
                }
            }
        }
        return $keys;
    }

    /**
     * Get Discounts before Tax
     *
     * @return array
     */
    public function getPreTaxDiscounts()
    {
        if (!$this->hasDiscounts()) {
            return [];
        }

        $discounts = [];
        foreach($this->getDiscounts() as $discountKey => $discount) {
            if ($discount->getIsPreTax()) {
                $discounts[$discountKey] = $discount;
            }
        }

        return $discounts;
    }

    /**
     * Get Discounts after Tax
     *  gets all discounts regardless of type
     *
     * @return array
     */
    public function getPostTaxDiscounts()
    {
        if (!$this->hasDiscounts()) {
            return [];
        }

        $discounts = [];
        foreach($this->getDiscounts() as $discountKey => $discount) {
            if (!$discount->getIsPreTax()) {
                $discounts[$discountKey] = $discount;
            }
        }

        return $discounts;
    }
}
