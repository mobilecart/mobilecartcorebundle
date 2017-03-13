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

class Shipment extends ArrayWrapper
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
            'id'              => 0,
            'company'         => '',
            'method'          => '',
            'code'            => '',
            'min_days'        => 0,
            'max_days'        => 0,
            'weight'          => 0,
            'price'           => 0,
            'is_taxable'      => false,
            'is_discountable' => false,
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['code'] = $this->getCode();
        return $data;
    }

    /**
     * Check whether this shipment validates a discount condition
     *
     * @param RuleCondition
     * @return bool
     */
    public function isValidCondition(RuleCondition $condition)
    {
        switch($condition->getSourceEntityField()) {
            case 'code':
                $condition->setSourceValue($this->getCode());
                break;
            case 'price':
                $condition->setSourceValue($this->getPrice());
                break;
            default:
                //no-op
                break;
        }

        return $condition->isValid();
    }

    /**
     * Check whether this shipment validates a hierarchy of discount conditions
     *
     * @param RuleConditionCompare
     * @return bool
     */
    public function isValidConditionCompare(RuleConditionCompare $compare)
    {
        return $compare->isValid($this);
    }

    /**
     * Retrieve a vendor/method code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->get('company') . '_' . $this->get('method');
    }
}
