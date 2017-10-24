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
 * Class Discount
 * @package MobileCart\CoreBundle\CartComponent
 */
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

    const PRODUCT = 'product';
    const SHIPMENT = 'shipment';
    const CUSTOMER = 'customer';
    const CART = 'cart';

    const OPERATOR_AND = 'and';
    const OPERATOR_OR = 'or';

    const ID = 'id';
    const NAME = 'name';
    const VALUE = 'value';
    const MAX_QTY = 'max_qty';
    const MAX_AMOUNT = 'max_amount';
    const IS_MAX_PER_ITEM = 'is_max_per_item';
    const APPLIED_AS = 'as';
    const APPLIED_AS_FLAT = 'flat';
    const APPLIED_AS_PERCENT = 'percent';
    const APPLIED_TO = 'to';
    const APPLIED_TO_SPECIFIED = 'specified';
    const APPLIED_TO_SHIPMENTS = 'shipments';
    const APPLIED_TO_ITEMS = 'items';
    const IS_COMPOUND = 'is_compound';
    const IS_PROPORTIONAL = 'is_proportional';
    const IS_PRE_TAX = 'is_pre_tax';
    const IS_AUTO = 'is_auto';
    const COUPON_CODE = 'coupon_code';
    const ITEMS = 'items';
    const SHIPMENTS = 'shipments';
    const PROMO_SKUS = 'promo_skus';
    const IS_STOPPER = 'is_stopper';
    const PRIORITY = 'priority';
    const START_TIME = 'start_time';
    const END_TIME = 'end_time';
    const PRE_CONDITIONS = 'pre_conditions';
    const TARGET_CONDITIONS = 'target_conditions';
    const PRE_CONDITION_COMPARE = 'pre_condition_compare';
    const TARGET_CONDITION_COMPARE = 'target_condition_compare';

    const TARGET_ALL_ITEMS = 'target_all_items';
    const TARGET_ALL_SHIPMENTS = 'target_all_shipments';

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
            self::ID                => 0,
            self::NAME              => '',
            self::VALUE             => 0,
            self::MAX_QTY           => 0,
            self::MAX_AMOUNT        => 0,
            self::IS_MAX_PER_ITEM   => false,
            self::APPLIED_AS        => self::APPLIED_AS_PERCENT,
            self::APPLIED_TO       => self::APPLIED_TO_SPECIFIED,
            self::IS_COMPOUND       => false,
            self::IS_PROPORTIONAL   => false,
            self::IS_PRE_TAX        => false,
            self::IS_AUTO           => false,
            self::COUPON_CODE       => '',
            self::ITEMS             => [],
            self::SHIPMENTS         => [],
            self::PROMO_SKUS        => [],
            self::IS_STOPPER        => false,
            self::PRIORITY          => self::$defaultPriority,
            self::START_TIME        => '',
            self::END_TIME          => '',
            self::PRE_CONDITIONS    => [],
            self::TARGET_CONDITIONS => [],
            self::PRE_CONDITION_COMPARE => null,
            self::TARGET_CONDITION_COMPARE => null,
        ];
    }

    /**
     * @param $id
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
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->data[self::NAME] = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->data[self::NAME];
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->data[self::VALUE] = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->data[self::VALUE];
    }

    /**
     * @param $maxQty
     * @return $this
     */
    public function setMaxQty($maxQty)
    {
        $this->data[self::MAX_QTY] = $maxQty;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxQty()
    {
        return $this->data[self::MAX_QTY];
    }

    /**
     * @param $maxAmount
     * @return $this
     */
    public function setMaxAmount($maxAmount)
    {
        $this->data[self::MAX_AMOUNT] = $maxAmount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxAmount()
    {
        return $this->data[self::MAX_AMOUNT];
    }

    /**
     * @param $isMaxPerItem
     * @return $this
     */
    public function setIsMaxPerItem($isMaxPerItem)
    {
        $this->data[self::IS_MAX_PER_ITEM] = $isMaxPerItem;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsMaxPerItem()
    {
        return $this->data[self::IS_MAX_PER_ITEM];
    }

    /**
     * @param string $appliedAs
     * @return $this
     */
    public function setAppliedAs($appliedAs)
    {
        $this->data[self::APPLIED_AS] = $appliedAs;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppliedAs()
    {
        return $this->data[self::APPLIED_AS];
    }

    /**
     * @param string $appliedTo
     * @return $this
     */
    public function setAppliedTo($appliedTo)
    {
        $this->data[self::APPLIED_TO] = $appliedTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getAppliedTo()
    {
        return $this->data[self::APPLIED_TO];
    }

    /**
     * @param bool $isCompound
     * @return $this
     */
    public function setIsCompound($isCompound)
    {
        $this->data[self::IS_COMPOUND] = (bool) $isCompound;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCompound()
    {
        return (bool) $this->data[self::IS_COMPOUND];
    }

    /**
     * @param bool $isProportional
     * @return $this
     */
    public function setIsProportional($isProportional)
    {
        $this->data[self::IS_PROPORTIONAL] = (bool) $isProportional;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsProportional()
    {
        return (bool) $this->data[self::IS_PROPORTIONAL];
    }

    /**
     * @param bool $isPreTax
     * @return $this
     */
    public function setIsPreTax($isPreTax)
    {
        $this->data[self::IS_PRE_TAX] = $isPreTax;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPreTax()
    {
        return (bool) $this->data[self::IS_PRE_TAX];
    }

    /**
     * @param bool $isAuto
     * @return $this
     */
    public function setIsAuto($isAuto)
    {
        $this->data[self::IS_AUTO] = (bool) $isAuto;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAuto()
    {
        return (bool) $this->data[self::IS_AUTO];
    }

    /**
     * @param string $couponCode
     * @return $this
     */
    public function setCouponCode($couponCode)
    {
        $this->data[self::COUPON_CODE] = $couponCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCouponCode()
    {
        return $this->data[self::COUPON_CODE];
    }

    public function addItem($item)
    {
        $productId = is_object($item) && $item instanceof Item
            ? $item->getProductId()
            : $item;

        $this->data[self::ITEMS][] = $productId;
        return $this;
    }

    public function setItems(array $items)
    {
        $this->data[self::ITEMS] = $items;
        return $this;
    }

    public function getItems()
    {
        return $this->data[self::ITEMS];
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function setProductIds(array $productIds)
    {
        $this->data[self::ITEMS] = $productIds;
        return $this;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function addProductId($productId)
    {
        $this->data[self::ITEMS][] = $productId;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->data[self::ITEMS];
    }

    public function addShipment($shipment)
    {
        $shipmentCode = is_object($shipment) && $shipment instanceof Shipment
            ? $shipment->getCode()
            : $shipment;

        $this->data[self::SHIPMENTS][] = $shipmentCode;
        return $this;
    }

    public function setShipments(array $shipments)
    {
        $this->data[self::SHIPMENTS] = $shipments;
        return $this;
    }

    public function getShipments()
    {
        return $this->data[self::SHIPMENTS];
    }

    /**
     * @param array $shipmentCodes
     * @return $this
     */
    public function setShipmentCodes(array $shipmentCodes)
    {
        $this->data[self::SHIPMENTS] = $shipmentCodes;
        return $this;
    }

    /**
     * @param string $shipmentCode
     * @return $this
     */
    public function addShipmentCode($shipmentCode)
    {
        $this->data[self::SHIPMENTS][] = $shipmentCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getShipmentCodes()
    {
        return $this->data[self::SHIPMENTS];
    }

    public function addPromoSku($sku)
    {
        $this->data[self::PROMO_SKUS][] = $sku;
        return $this;
    }

    public function setPromoSkus(array $promoSkus)
    {
        $this->data[self::PROMO_SKUS] = $promoSkus;
        return $this;
    }

    public function getPromoSkus()
    {
        return $this->data[self::PROMO_SKUS];
    }

    public function hasPromoSkus()
    {
        return count($this->getPromoSkus()) > 0;
    }

    public function setIsStopper($isStopper)
    {
        $this->data[self::IS_STOPPER] = $isStopper;
        return $this;
    }

    public function getIsStopper()
    {
        return $this->data[self::IS_STOPPER];
    }

    public function setPriority($priority)
    {
        $this->data[self::PRIORITY] = $priority;
        return $this;
    }

    public function getPriority()
    {
        return $this->data[self::PRIORITY];
    }

    public function setStartTime($startTime)
    {
        $this->data[self::START_TIME] = $startTime;
        return $this;
    }

    public function getStartTime()
    {
        return $this->data[self::START_TIME];
    }

    public function setEndTime($endTime)
    {
        $this->data[self::END_TIME] = $endTime;
        return $this;
    }

    public function getEndTime()
    {
        return $this->data[self::END_TIME];
    }

    /**
     * @param string $preConditions JSON string
     * @return $this
     */
    public function setPreConditions($preConditions)
    {
        $this->data[self::PRE_CONDITIONS] = $preConditions;
        return $this;
    }

    /**
     * @return string
     */
    public function getPreConditions()
    {
        return $this->data[self::PRE_CONDITIONS];
    }

    /**
     * @param string $targetConditions JSON string
     * @return $this
     */
    public function setTargetConditions($targetConditions)
    {
        $this->data[self::TARGET_CONDITIONS] = $targetConditions;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetConditions()
    {
        return $this->data[self::TARGET_CONDITIONS];
    }

    /**
     * @param RuleConditionCompare $preConditionCompare
     * @return $this
     */
    public function setPreConditionCompare(RuleConditionCompare $preConditionCompare)
    {
        $this->data[self::PRE_CONDITION_COMPARE] = $preConditionCompare;
        return $this;
    }

    /**
     * @return RuleConditionCompare
     */
    public function getPreConditionCompare()
    {
        return $this->data[self::PRE_CONDITION_COMPARE];
    }

    /**
     * @param RuleConditionCompare $targetConditionCompare
     * @return $this
     */
    public function setTargetConditionCompare(RuleConditionCompare $targetConditionCompare)
    {
        $this->data[self::TARGET_CONDITION_COMPARE] = $targetConditionCompare;
        return $this;
    }

    /**
     * @return RuleConditionCompare
     */
    public function getTargetConditionCompare()
    {
        return $this->data[self::TARGET_CONDITION_COMPARE];
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

        return array_merge(parent::toArray(), [
            self::PRE_CONDITIONS => $preConditionCompareData,
            self::TARGET_CONDITIONS => $targetConditionCompareData,
        ]);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        parent::fromArray($data);

        $as = isset($data[self::APPLIED_AS])
            ? $data[self::APPLIED_AS]
            : '';

        $this->data[self::APPLIED_AS] = ($as == self::APPLIED_AS_FLAT)
            ? self::APPLIED_AS_FLAT
            : self::APPLIED_AS_PERCENT;

        $to = isset($data[self::APPLIED_TO])
            ? $data[self::APPLIED_TO]
            : '';

        if (!in_array($to, [
            self::APPLIED_TO_SPECIFIED,
            self::APPLIED_TO_ITEMS,
            self::APPLIED_TO_SHIPMENTS
        ])) {
            $this->data[self::APPLIED_TO] = self::APPLIED_TO_ITEMS;
        }

        $startDatetime = isset($data[self::START_TIME])
            ? strtotime($data[self::START_TIME])
            : false;

        $endDatetime = isset($data[self::END_TIME])
            ? strtotime($data[self::END_TIME])
            : false;

        $this->data[self::START_TIME] = $startDatetime;
        $this->data[self::END_TIME] = $endDatetime;

        $toItems = isset($data[self::ITEMS])
            ? $data[self::ITEMS]
            : [];

        $toShipments = isset($data[self::SHIPMENTS])
            ? $data[self::SHIPMENTS]
            : [];

        $promoSkus = isset($data[self::PROMO_SKUS])
            ? $data[self::PROMO_SKUS]
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
        $this->data[self::PROMO_SKUS] = $promoSkus;

        $items = [];
        if (count($toItems) > 0) {
            foreach($toItems as $key) {
                $items[] = $key;
            }
        }
        $this->data[self::ITEMS] = $items;

        $shipments = [];
        if (count($toShipments) > 0) {
            foreach($toShipments as $key) {
                $shipments[] = $key;
            }
        }
        $this->data[self::SHIPMENTS] = $shipments;

        $preConditionJson = isset($data[self::PRE_CONDITIONS])
            ? $data[self::PRE_CONDITIONS]
            : '';

        $preCondition = null;
        if (strlen($preConditionJson)) {
            $preCondition = new RuleConditionCompare();
            $preCondition->fromJson($preConditionJson);
        }
        $this->data[self::PRE_CONDITION_COMPARE] = $preCondition;

        $targetConditionJson = isset($data[self::TARGET_CONDITIONS])
            ? $data[self::TARGET_CONDITIONS]
            : '';

        $targetCondition = null;
        if (strlen($targetConditionJson)) {
            $targetCondition = new RuleConditionCompare();
            $targetCondition->fromJson($targetConditionJson);
        }
        $this->data[self::TARGET_CONDITION_COMPARE] = $targetCondition;

        $this->data[self::COUPON_CODE] = isset($data[self::COUPON_CODE])
            ? $data[self::COUPON_CODE]
            : '';

        $this->data[self::IS_AUTO] = isset($data[self::IS_AUTO])
            ? $data[self::IS_AUTO]
            : false;

        $this->data[self::IS_STOPPER] = (bool) isset($data[self::IS_STOPPER])
            ? $data[self::IS_STOPPER]
            : false;

        $this->data[self::PRIORITY] = isset($data[self::PRIORITY])
            ? $data[self::PRIORITY]
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
        return ($this->getAppliedAs() == self::APPLIED_AS_FLAT);
    }
    
    /**
     * Retrieve whether this discount is a percentage
     *
     * @return bool
     */
    public function isPercent()
    {
        return ($this->getAppliedAs() == self::APPLIED_AS_PERCENT);
    }
    
    /**
     * Retrieve whether this discount applies to items
     */
    public function isToItems()
    {
        return ($this->getAppliedTo() == self::APPLIED_TO_ITEMS);
    }
    
    /**
     * Retrieve whether this discount applies to shipments
     */
    public function isToShipments()
    {
        return ($this->getAppliedTo() == self::APPLIED_TO_SHIPMENTS);
    }
    
    /**
     * Retrieve whether this discount applies to specific items,shipments
     */
    public function isToSpecified()
    {
        return ($this->getAppliedTo() == self::APPLIED_TO_SPECIFIED);
    }

    /**
     * @param $sku
     * @param int $qty
     * @return $this
     */
    public function setPromoSku($sku, $qty = 1)
    {
        $this->data[self::PROMO_SKUS][$sku] = $qty;
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
        $this->data[self::ITEMS] = $newItems;
        
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetItem($key)
    {
        if (isset($this->data[self::ITEMS][$key])) {
            unset($this->data[self::ITEMS][$key]);
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
        $this->data[self::SHIPMENTS] = $newShipments;
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
    protected function initItems()
    {
        if (!isset($this->data[self::ITEMS]) || !is_array($this->data[self::ITEMS])) {
            $this->data[self::ITEMS] = [];
        }

        return $this;
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
                            $this->addProductId($productId);
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
                        $this->addShipmentCode($methodCode);
                    }
                }
            }

            switch($this->get(self::APPLIED_TO)) {
                case self::APPLIED_TO_ITEMS:

                    // handle discount codes with promo skus
                    //  this is handled later in DiscountTotal
                    if ($this->getCouponCode() && $this->hasPromoSkus()) {
                        $cart->addDiscount($this);
                        return true;
                    }

                    // handle general item discount
                    if ($cart->hasItems()) {
                        $cart->addDiscount($this);
                        return true;
                    }

                    break;
                case self::APPLIED_TO_SHIPMENTS:
                    if ($cart->hasShipments()) {
                        $cart->addDiscount($this);
                        return true;
                    }
                    break;
                case self::APPLIED_TO_SPECIFIED:
                    if ($cart->hasShipments() || $cart->hasItems()) {
                        $cart->addDiscount($this);
                        return true;
                    }
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
    public function isValid(Cart $cart, array &$targets = [])
    {
        $targets[self::PRODUCT] = [];
        $targets[self::SHIPMENT] = [];

        if ($this->getStartTime() && time() < $this->getStartTime()) {
            return false;
        }

        if ($this->getEndTime() && time() > $this->getEndTime()) {
            return false;
        }

        switch($this->getAppliedTo()) {
            case self::APPLIED_TO_ITEMS:
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
            case self::APPLIED_TO_SHIPMENTS:
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

        // assuming:
        //  top level is and/or : RuleConditionCompare
        //  second level is product/shipment/customer : RuleConditionCompare
        //  3rd level is conditions : RuleCondition

        $preConditionObj = isset($data[self::PRE_CONDITIONS])
            ? $data[self::PRE_CONDITIONS]
            : null;

        $preCondition = null;
        if ($this->isToSpecified() && !is_null($preConditionObj)) {

            $conditionItems = []; // r[a] = [x, y, z] , a=index, x, y and z are Product IDs
            $conditionShipments = []; // r[a] = [x, y, z] , a=index, x, y and z are Shipment Codes

            $preCondition = new RuleConditionCompare();
            $preCondition->fromJson($preConditionObj);

            switch($preCondition->getOperator()) {
                case RuleConditionCompare::OP_HAS_CUSTOMER: // widget doesnt allow this value here
                case RuleConditionCompare::OP_HAS_SHIPMENT: // widget doesnt allow this value here
                case RuleConditionCompare::OP_HAS_PRODUCT: // widget doesnt allow this value here
                case RuleConditionCompare::OP_AND:

                    $x = 0;

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

                                        if ($shipments = $cart->getShipments()) {
                                            $conditionShipments[$x] = [];
                                            foreach($shipments as $shipment) {
                                                if ($shipment->isValidConditionCompare($subCondition)) {
                                                    $conditionShipments[$x][] = $shipment->getCode();
                                                }
                                            }

                                            if (!$conditionShipments[$x]) {
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
                                    case RuleConditionCompare::OP_CART_HAS:
                                        if (!$cart->isValidConditionCompare($subCondition)) {
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

                                            if ($conditionShipments[$x]) {
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

                    } else {
                        $conditionsMet = true;
                    }

                    if (!$conditionsMet) {
                        return false;
                    }

                    break;
                default:

                    break;
            }
        }

        $this->data[self::PRE_CONDITION_COMPARE] = $preCondition;

        $targetConditionObj = isset($data[self::TARGET_CONDITIONS])
            ? $data[self::TARGET_CONDITIONS]
            : null;

        $targetCondition = null;

        $targetItems = []; // r[a] = [x, y, z] , a=index, x, y and z are Product IDs
        $targetShipments = []; // r[a] = [x, y, z] , a=index, x, y and z are Shipment Codes

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
                                            $targetShipments[$x] = [];
                                            foreach($shipments as $shipment) {
                                                if ($shipment->isValidConditionCompare($subCondition)) {
                                                    $targetShipments[$x][] = $shipment->getCode();
                                                }
                                            }

                                            if (!$targetShipments[$x]) {
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

                                        if ($shipments = $cart->getShipments()) {
                                            $targetShipments[$x] = [];
                                            foreach($shipments as $shipment) {
                                                if ($shipment->isValidConditionCompare($subCondition)) {
                                                    $targetShipments[$x][] = $shipment->getCode();
                                                }
                                            }

                                            if ($targetShipments[$x]) {
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

                    } else {
                        $conditionsMet = true;
                    }

                    if (!$conditionsMet) {
                        return false;
                    }

                    break;
                case Discount::TARGET_ALL_ITEMS:
                    if ($items = $cart->getItems()) {
                        $x = 0;
                        $targetItems[$x] = [];
                        foreach($items as $item) {
                            if ($item->getIsDiscountable()) {
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
                case Discount::TARGET_ALL_SHIPMENTS:
                    if ($shipments = $cart->getShipments()) {
                        $x = 0;
                        $targetShipments[$x] = [];
                        foreach($shipments as $shipment) {
                            if ($shipment->getIsDiscountable()) {
                                $targetShipments[$x][] = $shipment->getCode();
                            }
                        }

                        if (!$targetShipments[$x]) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                    break;
                default:

                    break;
            }
        }
        $this->data[self::TARGET_CONDITION_COMPARE] = $targetCondition;

        $targets[self::PRODUCT] = $targetItems;
        $targets[self::SHIPMENT] = $targetShipments;

        return true;
    }


}
