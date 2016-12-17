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

class Cart extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{

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
            'id' => 0,
            'currency' => '',
            'customer' => new Customer(),
            'items' => [],
            'discounts' => [],
            'shipments' => [],
            'shipping_methods' => [],
            'include_tax' => false,
            'tax_rate' => 0.0,
            'precision' => 2,
            'calculator_precision' => 4,
            'discount_taxable_last' => true,
        ];
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
     * @return Shipment
     */
    public function createShipment()
    {
        return new Shipment();
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
            foreach($this->getShippingMethods() as $shipment) {
                $shipments[] = $shipment->toArray();
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
        if (isset($cart['id'])) {
            $this->setId($cart['id']);
        }

        if (isset($cart['customer'])) {
            $customerObj = $cart['customer'];
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

        if (isset($cart['items']) && count($cart['items']) > 0) {
            $items = $cart['items'];
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

        if (isset($cart['shipments']) && count($cart['shipments']) > 0) {
            $shipments = $cart['shipments'];
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

        if (isset($cart['discounts']) && count($cart['discounts']) > 0) {
            $discounts = $cart['discounts'];
            foreach($discounts as $discountObj) {

                $discountData = ($discountObj instanceof \stdClass)
                    ? get_object_vars($discountObj)
                    : (array) $discountObj;

                $discount = new Discount();
                $discount->fromArray($discountData);
                $this->addDiscount($discount);
            }
        }

        if (isset($cart['include_tax'])) {
            $includeTax = $cart['include_tax'];
            $this->setIncludeTax($includeTax);
        }

        if (isset($cart['tax_rate'])) {
            $taxRate = $cart['tax_rate'];
            $this->setTaxRate($taxRate);
        }

        if (isset($cart['discount_taxable_last'])) {
            $discountTaxableLast = $cart['discount_taxable_last'];
            $this->setDiscountTaxableLast($discountTaxableLast);
        }

        if ($cart) {
            foreach($cart as $k => $v) {
                if (in_array($k, ['id', 'customer', 'items', 'shipments', 'discounts', 'include_tax', 'tax_rate', 'discount_taxable_last'])) {
                    continue;
                }

                if ($v instanceof \stdClass) {
                    $cart[$k] = new ArrayWrapper(get_object_vars($v));
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
        /*
        Note: the Discount system is not using this yet
        */
        switch($condition->getSourceField()) {
            case 'total':
                $condition->setSourceValue($this->getCalculator()->getTotal());
                break;
            case 'item_total':
                $condition->setSourceValue($this->getCalculator()->getItemTotal());
                break;
            case 'shipment_total':
                $condition->setSourceValue($this->getCalculator()->getShipmentTotal());
                break;
            case 'discounted_item_total':
                $condition->setSourceValue($this->getCalculator()->getDiscountedItemTotal());
                break;
            case 'discounted_shipment_total':
                $condition->setSourceValue($this->getCalculator()->getDiscountedShipmentTotal());
                break;
            default:
                //no-op
                break;
        }

        return $condition->isValid();
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
     * @return null
     */
    public function getItem($idx)
    {
        return isset($this->data['items'][$idx])
            ? $this->data['items'][$idx]
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
     * @param $idx
     * @return $this
     */
    public function unsetItem($idx)
    {
        parent::unsetItem($idx); // magic method
        $items = $this->getItems();
        $this->setItems(array_values($items)); // array key handling for json encoding
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @param $addressId
     * @return int|null|string
     */
    public function findShipmentIdx($key, $value, $addressId='main')
    {
        if (!$this->hasShipments()) {
            return null;
        }

        // note: the customer_address_id is either 'main' or an integer
        //  it is not prefiex like in shipping_methods
        foreach($this->getShipments() as $idx => $shipment) {
            if ($shipment->get($key) == $value
                && ($addressId == '' || $shipment->get('customer_address_id') == $addressId )
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
     * @return bool|null
     */
    public function findShipment($key, $value, $addressId='main')
    {
        $idx = $this->findShipmentIdx($key, $value, $addressId);
        return is_numeric($idx)
            ? $this->getShipment($idx)
            : null;
    }

    /**
     * @param $method
     * @param string|int $addressId
     * @return $this
     */
    public function addShippingMethod($method, $addressId='main')
    {
        if (!isset($this->data['shipping_methods'])
            || !is_array($this->data['shipping_methods'])
        ) {
            $this->data['shipping_methods'] = [];
        }

        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if (!isset($this->data['shipping_methods'][$addressId])
            || !is_array($this->data['shipping_methods'][$addressId])
        ) {
            $this->data['shipping_methods'][$addressId] = [];
        }

        $this->data['shipping_methods'][$addressId][] = $method;
        return $this;
    }

    /**
     * @param string $addressId
     * @return array
     */
    public function getShippingMethods($addressId='main')
    {
        if ($addressId == '') {

            if (!isset($this->data['shipping_methods'])
                || !is_array($this->data['shipping_methods'])
            ) {
                return [];
            }

            return $this->data['shipping_methods'];
        }

        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if (!isset($this->data['shipping_methods'][$addressId])
            || !is_array($this->data['shipping_methods'][$addressId])
        ) {
            return [];
        }

        return $this->data['shipping_methods'][$addressId];
    }

    /**
     * @param $key
     * @param $value
     * @param $addressId
     * @return int|null|string
     */
    public function findShippingMethodIdx($key, $value, $addressId='main')
    {
        if (!$this->hasShippingMethods()) {
            return null;
        }

        // we prefix the integers so that we have a better string for a key
        //  this is only in shipping_methods, not in shipments
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if ($methods = $this->getShippingMethods($addressId)) {
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
     * @return bool|null
     */
    public function findShippingMethod($key, $value, $addressId='main')
    {
        $idx = $this->findShippingMethodIdx($key, $value, $addressId);
        return is_numeric($idx)
            ? $this->getShippingMethod($idx)
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
        return isset($this->data['items'][$key]);
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
     * @return bool|null
     */
    public function getItemByProductId($productId)
    {
        $idx = $this->findItemIdx('product_id', $productId);
        if (!is_numeric($idx)) {
            return false;
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
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function setProductQty($productId, $qty)
    {
        if ($this->hasProductId($productId)) {
            $idx = $this->findItemIdx('product_id', $productId);
            $this->data['items'][$idx]->setQty($qty);
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
            $this->data['items'][$idx]->setQty($qty);
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

                if ($discount->getTo() != Discount::$toSpecified) {
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
     * @return array
     */
    public function getDiscounts()
    {
        return $this->data['discounts'];
    }

    /**
     * @param $key
     * @return bool
     */
    public function getDiscount($key)
    {
        return isset($this->data['discounts'][$key])
            ? $this->data['discounts'][$key]
            : false;
    }

    /**
     * @param $key
     * @param Discount $discount
     * @return $this
     */
    public function setDiscount($key, Discount $discount)
    {
        $this->data['discounts'][$key] = $discount;
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
            unset($this->data['discounts'][$idx]);
        }
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetDiscount($key)
    {
        if (isset($this->data['discounts'][$key])) {
            unset($this->data['discounts'][$key]);
        }

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasDiscountId($key)
    {
        return is_numeric($this->findDiscountIdx('id', $key));
    }

    /**
     * @param $code
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
        return isset($this->data['shipments'][$key])
            ? $this->data['shipments'][$key]
            : null;
    }

    /**
     * @param $key
     * @param $addressId
     * @return bool
     */
    public function getShippingMethod($key, $addressId='main')
    {
        if ($addressId != 'main' && is_numeric($addressId)) {
            $addressId = 'address_' . $addressId; // prefixing integers
        }

        if (!isset($this->data['shipping_methods'][$addressId])
            || !is_array($this->data['shipping_methods'][$addressId])
        ) {
            return null;
        }

        return isset($this->data['shipping_methods'][$addressId][$key])
            ? $this->data['shipping_methods'][$addressId][$key]
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
        $this->data['shipments'][$idx] = $shipment;
        // being tedious with json structure here
        $this->data['shipments'] = array_values($this->data['shipments']);
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param array $shipments
     * @return $this
     */
    public function setShipments(array $shipments = [])
    {
        $this->data['shipments'] = array_values($shipments);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function removeShipmentById($id)
    {
        $idx = $this->findShipmentIdx('id', $id);
        if (is_numeric($idx)
            && isset($this->data['shipments'][$idx])) {

            unset($this->data['shipments'][$idx]);
        }
        $this->data['shipments'] = array_values($this->data['shipments']); // strip keys for json structure
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $code
     * @return $this
     */
    public function removeShipmentByCode($code)
    {
        $idx = $this->findShipmentIdx('code', $code);
        if (is_numeric($idx)
            && isset($this->data['shipments'][$idx])) {

            unset($this->data['shipments'][$idx]);
        }
        $this->data['shipments'] = array_values($this->data['shipments']); // strip keys for json structure
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetShipment($key)
    {
        if (isset($this->data['shipments'][$key])) {
            unset($this->data['shipments'][$key]);
        }
        $this->data['shipments'] = array_values($this->data['shipments']); // strip keys for json structure
        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $key
     * @param $addressId
     * @return $this
     */
    public function unsetShippingMethod($key, $addressId='main')
    {
        if (isset($this->data['shipping_methods'][$addressId][$key])) {
            unset($this->data['shipping_methods'][$addressId][$key]);
        }

        // dont need to remove anything from discounts
        $this->data['shipments'] = array_values($this->data['shipments']); // strip keys for json structure

        return $this;
    }

    /**
     * @param $addressId
     * @return bool
     */
    public function addressHasShipment($addressId)
    {
        if ($shipments = $this->getShipments()) {
            foreach($shipments as $shipment) {
                if ($shipment->get('customer_address_id') == $addressId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $addressId
     * @return $this
     */
    public function unsetShipments($addressId='')
    {
        if ($addressId) {
            if ($this->hasShipments()) {
                foreach($this->getShipments() as $idx => $shipment) {
                    if ($shipment->get('customer_address_id') == $addressId) {
                        unset($this->data['shipments'][$idx]);
                    }
                }

                $this->data['shipments'] = array_values($this->data['shipments']); // strip keys for json structure
            }
        } else {
            $this->setShipments([]);
        }

        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $addressId
     * @return $this
     */
    public function unsetShippingMethods($addressId='')
    {
        if ($addressId) {
            if ($this->hasShippingMethods()) {
                foreach($this->getShippingMethods() as $anAddressId => $methods) {
                    if (!$methods || $addressId != $anAddressId) {
                        continue;
                    }
                    $this->data['shipping_methods'][$addressId] = [];
                }
            }
        } else {
            $this->setShippingMethods([]);
        }

        $this->reapplyDiscounts();
        return $this;
    }

    /**
     * @param $id
     * @param $addressId
     * @return bool
     */
    public function hasShipmentMethodId($id, $addressId='main')
    {
        return is_numeric($this->findShipmentIdx('id', $id, $addressId));
    }

    /**
     * Find Shipment added to the cart
     *
     * @param $code
     * @param $addressId
     * @return bool
     */
    public function hasShipmentMethodCode($code, $addressId='main')
    {
        if ($addressId != 'main' && !is_numeric($addressId)) {
            $addressId = (int) str_replace('address_', '', $addressId);
        }
        return is_numeric($this->findShipmentIdx('code', $code, $addressId));
    }

    /**
     * Find Shipping Method which might get added to the Cart as a Shipment
     *
     * @param $code
     * @param $addressId
     * @return bool
     */
    public function hasShippingMethodCode($code, $addressId='main')
    {
        return is_numeric($this->findShippingMethodIdx('code', $code, $addressId));
    }

    /**
     * @param $id
     * @param $addressId
     * @return bool
     */
    public function hasShippingMethodId($id, $addressId='main')
    {
        return is_numeric($this->findShippingMethodIdx('id', $id, $addressId));
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
                if ($discount->getTo() != Discount::$toSpecified) {
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
