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
            'is_stopper'        => false,
            'priority'          => self::$defaultPriority,
            'start_datetime'    => '',
            'end_datetime'      => '',
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

        $startDatetime = ''; //TODO
        $endDatetime = ''; //TODO

        $this->data['start_datetime'] = $startDatetime;
        $this->data['end_datetime'] = $endDatetime;

        $toItems = isset($data['items'])
            ? $data['items']
            : [];

        $toShipments = isset($data['shipments'])
            ? $data['shipments']
            : [];

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
     * Remove an Item from this Discount
     *
     * @param string
     * @return Discount
     */
    public function unsetItem($key)
    {
        if ($key instanceof Item) {
            $key = Item::getKey($key->getId());
        }
        
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
     * Assert item is specified in this discount
     *
     * @param {String} key
     * @return boolean hasItem
     */
    public function hasItem($key)
    {
        return in_array($key, $this->getItems());
    }

//    /**
//     * Whether this discount has specified items
//     *
//     * @return bool
//     */
//    public function hasItems()
//    {
//        return (count($this->getItems()) > 0);
//    }

    /**
     * Remove a specified Shipment from this Discount
     *
     * @param string
     * @return Discount
     */
    public function unsetShipment($key)
    {
        if ($key instanceof Shipment) {
            $key = Shipment::getKey($key->getId());
        }
        
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
     * Assert shipment is specified in this discount
     *
     * @param string
     * @return bool
     */
    public function hasShipment($key)
    {
        return in_array($key, $this->getShipments());
    }

//    /**
//     * Assert this discounts has specified shipments
//     *
//     * @return bool
//     */
//    public function hasShipments()
//    {
//        return (count($this->getShipments()) > 0);
//    }
}
