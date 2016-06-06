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

class Discount extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    /*

    TODO:
    - look at applying to cart rather than items
     - related options would follow
    - Cheapest, Lowest/Highest Qty, etc

    //*/

    // array key values
    static $defaultPriority = 1000;
    static $asFlat = 'flat';
    static $asPercent = 'percent';
    static $toSpecified = 'specified';
    static $toShipments = 'shipments';
    static $toItems = 'items';
    static $prefix = 'discount-'; // array key prefix

    const PRODUCT = 'product';
    const SHIPMENT = 'shipment';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct($this->getDefaults());
    }

    /**
     * Get a prefixed key for associative arrays
     *
     * @param int
     * @return string
     */
    static function getKey($id)
    {
        return self::$prefix . $id;
    }

    public function getDefaults()
    {
        return [
            'id'                => 0,
            'name'              => '',
            'value'             => 0,
            'max_qty'           => 0,
            'max_amount'        => 0,
            'is_max_per_item'   => false,
            'as'                => self::$asPercent,
            'to'                => self::$toSpecified,
            'is_compound'       => false,
            'is_proportional'   => false,
            'is_pre_tax'        => false,
            'is_auto'           => false,
            'coupon_code'       => '',
            'items'             => [],
            'shipments'         => [],
            'promo_skus'        => [],
            'is_stopper'        => false,
            'priority'          => self::$defaultPriority,
            'start_time'        => '',
            'end_time'          => '',
            'pre_conditions'    => [],
            'target_conditions' => [],
        ];
    }

    /**
     * Export as array
     *
     * @return array
     */
    public function toArray()
    {
        $preConditionCompareData = null;
        if ($this->getPreConditionCompare() instanceof RuleConditionCompare) {
            $preConditionCompareData = $this->getPreConditionCompare()->toArray();
        }

        $targetConditionCompareData = null;
        if (is_object($this->getTargetConditionCompare())) {
            $targetConditionCompareData = $this->getTargetConditionCompare()->toArray();
        }

        return array_merge(parent::toArray(), array(
            'pre_conditions' => $preConditionCompareData,
            'target_conditions' => $targetConditionCompareData,
        ));
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        parent::fromArray($data);

        $as = isset($data['as'])
            ? $data['as']
            : '';

        $this->data['as'] = ($as == self::$asFlat)
            ? self::$asFlat
            : self::$asPercent;

        $to = isset($data['to'])
            ? $data['to']
            : '';

        if (!in_array($to, [self::$toSpecified, self::$toItems, self::$toShipments])) {
            $this->data['to'] = self::$toItems;
        }

        $startDatetime = isset($data['start_time'])
            ? strtotime($data['start_time'])
            : false;

        $endDatetime = isset($data['end_time'])
            ? strtotime($data['end_time'])
            : false;

        $this->data['start_time'] = $startDatetime;
        $this->data['end_time'] = $endDatetime;

        $toItems = isset($data['items'])
            ? $data['items']
            : [];

        $toShipments = isset($data['shipments'])
            ? $data['shipments']
            : [];

        $promoSkus = isset($data['promo_skus'])
            ? $data['promo_skus']
            : [];

        if (is_string($promoSkus)) {

            // handle JSON or CSV
            if (is_int(strpos($promoSkus, ':'))
                && is_int(strpos($promoSkus, '}'))
                && is_int(strpos($promoSkus, '{'))
            ) {
                $promoSkus = @ (array) json_decode($promoSkus);
                // sku data should have been validated before it was saved
            } else if (is_int(strpos($promoSkus, ','))) {
                $skus = explode(',', $promoSkus);
                if ($skus) {
                    foreach($skus as $sku) {
                        $sku = trim($sku);
                        $promoSkus[$sku] = 1;
                    }
                }
            } else {
                $promoSkus = [$promoSkus];
            }
        }

        if ($promoSkus) {

            $newPromoSkus = [];

            // array has 2 possible formats:
            //  r[x] = 'abc' , in which case: the quantity is always 1
            //  r['abc'] = y , in which case: the quantity is the value, and the sku is the key

            $counter = 0;
            foreach($promoSkus as $k => $v) {

                if (!is_scalar($v)) {
                    unset($promoSkus[$k]);
                    continue;
                }

                $qty = 1;
                $sku = '';
                if ($k == $counter) {
                    $sku = $v;
                } else {
                    $sku = $k;
                    $qty = (int) $v;

                    if ($qty < 1) {
                        $qty = 1;
                    }
                }

                $newPromoSkus[$sku] = $qty;
                $counter++;
            }

            $promoSkus = $newPromoSkus;
        }
        $this->data['promo_skus'] = $promoSkus;

        $items = [];
        if (count($toItems) > 0) {
            foreach($toItems as $key) {
                $items[] = $key;
            }
        }
        $this->data['items'] = $items;

        $shipments = [];
        if (count($toShipments) > 0) {
            foreach($toShipments as $key) {
                $shipments[] = $key;
            }
        }
        $this->data['shipments'] = $shipments;

        $preConditionObj = isset($data['pre_conditions'])
            ? $data['pre_conditions']
            : null;

        $preCondition = null;
        if (!is_null($preConditionObj)) {
            $preCondition = new RuleConditionCompare();
            $preCondition->fromJson(json_encode($preConditionObj));
        }
        $this->data['pre_condition_compare'] = $preCondition;

        $targetConditionObj = isset($data['target_conditions'])
            ? $data['target_conditions']
            : null;

        $targetCondition = null;
        if (!is_null($targetConditionObj)) {
            $targetCondition = new RuleConditionCompare();
            $targetCondition->fromJson(json_encode($targetConditionObj));
        }
        $this->data['target_condition_compare'] = $targetCondition;

        $this->data['coupon_code'] = isset($data['coupon_code'])
            ? $data['coupon_code']
            : '';

        $this->data['is_auto'] = isset($data['is_auto'])
            ? $data['is_auto']
            : false;

        $this->data['is_stopper'] = (bool) isset($data['is_stopper'])
            ? $data['is_stopper']
            : false;

        $this->data['priority'] = isset($data['priority'])
            ? $data['priority']
            : self::$defaultPriority;

        return $this;
    }
    
    /**
     * Retrieve whether this discount is flat
     *
     * @return bool
     */
    public function isFlat()
    {
        return ($this->getAs() == self::$asFlat);
    }
    
    /**
     * Retrieve whether this discount is a percentage
     *
     * @return bool
     */
    public function isPercent()
    {
        return ($this->getAs() == self::$asPercent);
    }
    
    /**
     * Retrieve whether this discount applies to items
     */
    public function isToItems()
    {
        return ($this->getTo() == self::$toItems);
    }
    
    /**
     * Retrieve whether this discount applies to shipments
     */
    public function isToShipments()
    {
        return ($this->getTo() == self::$toShipments);
    }
    
    /**
     * Retrieve whether this discount applies to specific items,shipments
     */
    public function isToSpecified()
    {
        return ($this->getTo() == self::$toSpecified);
    }

    /**
     * @param $sku
     * @param int $qty
     * @return $this
     */
    public function setPromoSku($sku, $qty = 1)
    {
        $this->data['promo_skus'][$sku] = $qty;
        return $this;
    }

    /**
     * Remove an Item from this Discount
     *
     * @param string
     * @return Discount
     */
    public function unsetItemByProductId($key)
    {
        if (!count($this->getItems())) {
            return $this;
        }
        
        $newItems = array_flip($this->getItems());
        unset($newItems[$key]);
        $newItems = array_flip($newItems);
        $this->data['items'] = $newItems;
        
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetItem($key)
    {
        if (isset($this->data['items'][$key])) {
            unset($this->data['items'][$key]);
        }
        return $this;
    }

    /**
     * Assert item is specified in this discount
     *
     * @param string $key
     * @return boolean hasItem
     */
    public function hasItem($key)
    {
        $items = $this->getItems();
        return isset($items[$key]);
    }

    /**
     * @param $productId
     * @return bool
     */
    public function hasProductId($productId)
    {
        if ($this->hasItems()) {
            foreach($this->getItems() as $aProductId) {
                if (!is_numeric($aProductId)) {
                    continue;
                }
                if ($aProductId == $productId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $code
     * @return bool
     */
    public function hasShipmentCode($code)
    {
        if ($this->hasShipments()) {
            foreach($this->getShipments() as $shipment) {
                if ($shipment->getCode() == $code) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Remove a specified Shipment from this Discount
     *
     * @param string
     * @return Discount
     */
    public function unsetShipmentCode($key)
    {
        if (!count($this->getShipments())) {
            return $this;
        }
        
        $newShipments = array_flip($this->getShipments());
        unset($newShipments[$key]);
        $newShipments = array_flip($newShipments);
        $this->data['shipments'] = $newShipments;
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
        return $this;
    }

    /**
     * Assert shipment is specified in this discount
     *
     * @param string
     * @return bool
     */
    public function hasShipment($key)
    {
        return in_array($key, $this->getShipments());
    }

    /**
     * @param Cart $cart
     * @return bool
     */
    public function reapplyIfValid(Cart &$cart)
    {
        if ($cart->hasDiscountId($this->getId())) {
            $cart->removeDiscountId($this->getId());
        }

        return $this->applyIfValid($cart);
    }

    /**
     * @param Cart $cart
     * @return bool
     */
    public function applyIfValid(Cart &$cart)
    {
        $targets = [];
        if (
            $this->isValid($cart, $targets)
            && !$cart->hasDiscountId($this->getId())
        ) {
            if ($targets[self::PRODUCT]) {
                foreach($targets[self::PRODUCT] as $conditions) {
                    foreach($conditions as $productId) {
                        $item = $cart->findItem('product_id', $productId);
                        if (
                            $item
                            && !$this->hasProductId($productId)
                        ) {
                            $this->addItem($productId);
                        }
                    }
                }
            }

            if ($targets[self::SHIPMENT]) {
                foreach($targets[self::SHIPMENT] as $methodCode) {
                    $shipment = $cart->findShipment('code', $methodCode);
                    if (
                        $shipment
                        && !$this->hasShipmentCode($methodCode)
                    ) {
                        $this->addShipment($methodCode);
                    }
                }
            }

            switch($this->get('to')) {
                case self::$toItems:
                    if ($this->getCouponCode() && $this->hasPromoSkus()) {
                        return true;
                    }
                    return $cart->hasItems();
                    break;
                case self::$toShipments:
                    return $cart->hasShipments();
                    break;
                case self::$toSpecified:
                    return $cart->hasShipments() || $cart->hasItems();
                    break;
                default:

                    break;
            }

        }

        return false;
    }

    /**
     * @param Cart $cart
     * @param array $targets , array of product IDs and shipment Codes
     * @return bool
     */
    public function isValid(Cart &$cart, array &$targets = [])
    {
        $targets[self::PRODUCT] = [];
        $targets[self::SHIPMENT] = [];

        if ($this->getStartTime() && time() < $this->getStartTime()) {
            return false;
        }

        if ($this->getEndTime() && time() > $this->getEndTime()) {
            return false;
        }

        switch($this->getTo()) {
            case self::$toItems:
                if (!$cart->hasItems()) {
                    return false;
                }
                $hasDiscountable = false;
                foreach($cart->getItems() as $item) {
                    if ($item->getIsDiscountable()) {
                        $hasDiscountable = true;
                    }
                }
                if (!$hasDiscountable) {
                    return false;
                }
                break;
            case self::$toShipments:
                if (!$cart->hasShipments()) {
                    return false;
                }
                $hasDiscountable = false;
                foreach($cart->getShipments() as $shipment) {
                    if ($shipment->getIsDiscountable()) {
                        $hasDiscountable = true;
                    }
                }
                if (!$hasDiscountable) {
                    return false;
                }
                break;
            default:

                break;
        }

        $data = $this->data;
        $targetItems = [];
        $targetShipments = [];

        // assuming:
        //  top level is and/or : RuleConditionCompare
        //  second level is product/shipment/customer : RuleConditionCompare
        //  3rd level is conditions : RuleCondition

        $preConditionObj = isset($data['pre_conditions'])
            ? $data['pre_conditions']
            : null;

        $preCondition = null;
        if ($this->isToSpecified() && !is_null($preConditionObj)) {

            $preCondition = new RuleConditionCompare();
            $preCondition->fromJson($preConditionObj);

            switch($preCondition->getOperator()) {
                case RuleConditionCompare::OP_HAS_CUSTOMER: // widget doesnt allow this value here
                case RuleConditionCompare::OP_HAS_SHIPMENT: // widget doesnt allow this value here
                case RuleConditionCompare::OP_HAS_PRODUCT: // widget doesnt allow this value here
                case RuleConditionCompare::OP_AND:

                    $x = 0;
                    $conditionItems = []; // r[a] = [x, y, z] , a=index, x, y and z are Product IDs
                    $conditionShipments = []; // r[a] = [x, y, z] , a=index, x, y and z are Shipment Codes

                    // _all_ of the conditions must be valid, so we can return false on the first invalid condition
                    if ($preConditions = $preCondition->getConditions()) {

                        // "ideally", these are all "Cart has a X" ConditionCompare operators
                        foreach($preConditions as $subCondition) {

                            // each condition should be fulfilled by 1 or more objects
                            //  eg 1 or more products
                            //  the customer
                            //  1 or more shipments, usually just 1

                            if ($subCondition instanceof RuleConditionCompare) {

                                switch($subCondition->getOperator()) {
                                    case RuleConditionCompare::OP_HAS_CUSTOMER:

                                        $customer = $cart->getCustomer();
                                        if (!$customer->isValidConditionCompare($subCondition)) {
                                            return false;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_SHIPMENT:

                                        // note: default store only supports a single shipment
                                        if ($shipments = $cart->getShipments()) {
                                            $conditionShipments[$x] = [];
                                            foreach($shipments as $shipment) {
                                                if ($shipment->isValidConditionCompare($subCondition)) {
                                                    $conditionShipments[$x][] = $shipment->getCode();
                                                }
                                            }

                                            if (!$conditionItems[$x]) {
                                                return false;
                                            }
                                        } else {
                                            return false;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_PRODUCT:

                                        if ($items = $cart->getItems()) {
                                            $conditionItems[$x] = [];
                                            foreach($items as $item) {
                                                if ($item->isValidConditionCompare($subCondition)) {
                                                    $conditionItems[$x][] = $item->getProductId();
                                                }
                                            }

                                            if (!$conditionItems[$x]) {
                                                return false;
                                            }
                                        } else {
                                            return false;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_AND: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    case RuleConditionCompare::OP_OR: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    default:

                                        break;
                                }
                            } else {

                                // enforcing the widget architecture
                                return false;
                            }

                            $x++;
                        }

                        // at this point, we can assume we have valid cart objects
                        // but, we cannot assume an object is not in multiple "slots"
                        //  basically, a single sku shouldn't fulfill more than 1 condition
                        // TODO: make an option which enables a unique filter to be executed
                        // eg 'product_unique' , 'shipment_unique'

                    }

                    break;
                case RuleConditionCompare::OP_OR:

                    // Note:
                    //  only a single condition needs to be valid
                    $conditionsMet = false;

                    $x = 0;
                    $conditionItems = []; // r[a] = [x, y, z] , a=index, x, y and z are Product IDs
                    $conditionShipments = []; // r[a] = [x, y, z] , a=index, x, y and z are Shipment Codes

                    if ($preConditions = $preCondition->getConditions()) {

                        // "ideally", these are all "Cart has a X" ConditionCompare operators
                        foreach($preConditions as $subCondition) {

                            // each condition should be fulfilled by 1 or more objects
                            //  eg 1 or more products
                            //  the customer
                            //  1 or more shipments, usually just 1

                            if ($subCondition instanceof RuleConditionCompare) {

                                switch($subCondition->getOperator()) {
                                    case RuleConditionCompare::OP_HAS_CUSTOMER:

                                        $customer = $cart->getCustomer();
                                        if ($customer->isValidConditionCompare($subCondition)) {
                                            $conditionsMet = true;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_SHIPMENT:

                                        // note: default store only supports a single shipment
                                        if ($shipments = $cart->getShipments()) {
                                            $conditionShipments[$x] = [];
                                            foreach($shipments as $shipment) {
                                                if ($shipment->isValidConditionCompare($subCondition)) {
                                                    $conditionShipments[$x][] = $shipment->getCode();
                                                }
                                            }

                                            if ($conditionItems[$x]) {
                                                $conditionsMet = true;
                                            }
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_PRODUCT:


                                        if ($items = $cart->getItems()) {
                                            $conditionItems[$x] = [];
                                            foreach($items as $item) {
                                                if ($item->isValidConditionCompare($subCondition)) {
                                                    $conditionItems[$x][] = $item->getProductId();
                                                }
                                            }

                                            if ($conditionItems[$x]) {
                                                $conditionsMet = true;
                                            }
                                        }

                                        break;
                                    case RuleConditionCompare::OP_AND: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    case RuleConditionCompare::OP_OR: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    default:

                                        break;
                                }
                            } else {

                                // enforcing the widget architecture
                                return false;
                            }

                            $x++;
                        }

                        // at this point, we can assume we have valid cart objects
                        // but, we cannot assume an object is not in multiple "slots"
                        //  basically, a single sku shouldn't fulfill more than 1 condition
                        // TODO: make an option which enables a unique filter to be executed
                        // eg 'product_unique' , 'shipment_unique'

                    }

                    if (!$conditionsMet) {
                        return false;
                    }

                    break;
                default:

                    break;
            }
        }

        $this->data['pre_condition_compare'] = $preCondition;

        $targetConditionObj = isset($data['target_conditions'])
            ? $data['target_conditions']
            : null;

        $targetCondition = null;
        if ($this->isToSpecified() && !is_null($targetConditionObj)) {
            $targetCondition = new RuleConditionCompare();
            $targetCondition->fromJson($targetConditionObj);

            switch($targetCondition->getOperator()) {
                case RuleConditionCompare::OP_HAS_CUSTOMER: // widget doesnt allow this value here
                case RuleConditionCompare::OP_HAS_SHIPMENT: // widget doesnt allow this value here
                case RuleConditionCompare::OP_HAS_PRODUCT: // widget doesnt allow this value here
                case RuleConditionCompare::OP_AND:

                    $x = 0;

                    // _all_ of the conditions must be valid, so we can return false on the first invalid condition
                    if ($targetConditions = $targetCondition->getConditions()) {

                        // "ideally", these are all "Cart has a X" ConditionCompare operators
                        foreach($targetConditions as $subCondition) {

                            // each condition should be fulfilled by 1 or more objects
                            //  eg 1 or more products
                            //  the customer
                            //  1 or more shipments, usually just 1

                            if ($subCondition instanceof RuleConditionCompare) {

                                switch($subCondition->getOperator()) {
                                    case RuleConditionCompare::OP_HAS_CUSTOMER:

                                        $customer = $cart->getCustomer();
                                        if (!$customer->isValidConditionCompare($subCondition)) {
                                            return false;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_SHIPMENT:

                                        // note: default store only supports a single shipment
                                        if ($shipments = $cart->getShipments()) {
                                            $conditionShipments[$x] = [];
                                            foreach($shipments as $shipment) {
                                                if ($shipment->isValidConditionCompare($subCondition)) {
                                                    $conditionShipments[$x][] = $shipment->getCode();
                                                }
                                            }

                                            if (!$targetItems[$x]) {
                                                return false;
                                            }
                                        } else {
                                            return false;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_PRODUCT:


                                        if ($items = $cart->getItems()) {
                                            $targetItems[$x] = [];
                                            foreach($items as $item) {
                                                if ($item->isValidConditionCompare($subCondition)) {
                                                    $targetItems[$x][] = $item->getProductId();
                                                }
                                            }

                                            if (!$targetItems[$x]) {
                                                return false;
                                            }
                                        } else {
                                            return false;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_AND: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    case RuleConditionCompare::OP_OR: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    default:

                                        break;
                                }
                            } else {

                                // enforcing the widget data structure
                                return false;
                            }

                            $x++;
                        }

                        // at this point, we can assume we have valid cart objects
                        // but, we cannot assume an object is not in multiple "slots"
                        //  basically, a single sku shouldn't fulfill more than 1 condition
                        // TODO: make an option which enables a unique filter to be executed
                        // eg 'product_unique' , 'shipment_unique'

                    }

                    break;
                case RuleConditionCompare::OP_OR:

                    // Note:
                    //  only a single condition needs to be valid
                    $conditionsMet = false;

                    $x = 0;

                    if ($targetConditions = $targetCondition->getConditions()) {

                        // "ideally", these are all "Cart has a X" ConditionCompare operators
                        foreach($targetConditions as $subCondition) {

                            // each condition should be fulfilled by 1 or more objects
                            //  eg 1 or more products
                            //  the customer
                            //  1 or more shipments, usually just 1

                            if ($subCondition instanceof RuleConditionCompare) {

                                switch($subCondition->getOperator()) {
                                    case RuleConditionCompare::OP_HAS_CUSTOMER:

                                        $customer = $cart->getCustomer();
                                        if ($customer->isValidConditionCompare($subCondition)) {
                                            $conditionsMet = true;
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_SHIPMENT:

                                        // note: default store only supports a single shipment
                                        if ($shipments = $cart->getShipments()) {
                                            $conditionShipments[$x] = [];
                                            foreach($shipments as $shipment) {
                                                if ($shipment->isValidConditionCompare($subCondition)) {
                                                    $conditionShipments[$x][] = $shipment->getCode();
                                                }
                                            }

                                            if ($targetItems[$x]) {
                                                $conditionsMet = true;
                                            }
                                        }

                                        break;
                                    case RuleConditionCompare::OP_HAS_PRODUCT:


                                        if ($items = $cart->getItems()) {
                                            $targetItems[$x] = [];
                                            foreach($items as $item) {
                                                if ($item->isValidConditionCompare($subCondition)) {
                                                    $targetItems[$x][] = $item->getProductId();
                                                }
                                            }

                                            if ($targetItems[$x]) {
                                                $conditionsMet = true;
                                            }
                                        }

                                        break;
                                    case RuleConditionCompare::OP_AND: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    case RuleConditionCompare::OP_OR: // widget doesnt allow this value here
                                        return false;
                                        break;
                                    default:

                                        break;
                                }
                            } else {

                                // enforcing the widget architecture
                                return false;
                            }

                            $x++;
                        }

                        // at this point, we can assume we have valid cart objects
                        // but, we cannot assume an object is not in multiple "slots"
                        //  basically, a single sku shouldn't fulfill more than 1 condition
                        // TODO: make an option which enables a unique filter to be executed
                        // eg 'product_unique' , 'shipment_unique'

                    }

                    if (!$conditionsMet) {
                        return false;
                    }

                    break;
                default:

                    break;
            }
        }
        $this->data['target_condition_compare'] = $targetCondition;

        $targets[self::PRODUCT] = $targetItems;
        $targets[self::SHIPMENT] = $targetShipments;

        return true;
    }


}
