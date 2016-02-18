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

class Item extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{

    static $prefix = 'item-';

    /**
     * Get key for associative arrays
     */
    static function getKey($itemId)
    {
        return self::$prefix . $itemId;
    }

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
            'id'              => 0,
            'sku'             => '',
            'name'            => '',
            'slug'            => '',
            'image'           => '',
            'price'           => 0,
            'qty'             => 0,
            'category_ids'    => [],
            'custom'          => [],
            'is_taxable'      => false,
            'is_discountable' => true,
        ];
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        $total = $this->getPrice() * $this->getQty();
        return number_format($total, 4, '.', '');
    }

    /**
     * Check if this Item validates a condition
     *
     * @param RuleCondition
     * @return bool
     */
    public function isValidCondition(RuleCondition $condition)
    {
        $condition->setSourceValue($this->get($condition->getEntityField()));
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
}
