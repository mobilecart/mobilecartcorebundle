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
 * Class Shipment
 * @package MobileCart\CoreBundle\CartComponent
 */
class Shipment extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    const ID = 'id';
    const SHIPPING_METHOD_ID = 'shipping_method_id';
    const COMPANY = 'company';
    const METHOD = 'method';
    const CODE = 'code';
    const MIN_DAYS = 'min_days';
    const MAX_DAYS = 'max_days';
    const WEIGHT = 'weight';
    const PRICE = 'price';
    const IS_TAXABLE = 'is_taxable';
    const IS_DISCOUNTABLE = 'is_discountable';

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
            self::ID                 => 0,
            self::SHIPPING_METHOD_ID => 0,
            self::COMPANY            => '',
            self::METHOD             => '',
            self::CODE               => '',
            self::MIN_DAYS           => 0,
            self::MAX_DAYS           => 0,
            self::WEIGHT             => 0,
            self::PRICE              => 0,
            self::IS_TAXABLE         => false,
            self::IS_DISCOUNTABLE    => false,
        ];
    }

    /**
     * Set the row id
     *
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
     * @param $shippingMethodId
     * @return $this
     */
    public function setShippingMethodId($shippingMethodId)
    {
        $this->data[self::SHIPPING_METHOD_ID] = $shippingMethodId;
        return $this;
    }

    /**
     * @return int
     */
    public function getShippingMethodId()
    {
        return $this->data[self::SHIPPING_METHOD_ID];
    }

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->data[self::COMPANY] = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->data[self::COMPANY];
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->data[self::METHOD] = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->data[self::METHOD];
    }

    /**
     * @param int $minDays
     * @return $this
     */
    public function setMinDays($minDays)
    {
        $this->data[self::MIN_DAYS] = (int) $minDays;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinDays()
    {
        return (int) $this->data[self::MIN_DAYS];
    }

    /**
     * @param int $maxDays
     * @return $this
     */
    public function setMaxDays($maxDays)
    {
        $this->data[self::MAX_DAYS] = (int) $maxDays;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxDays()
    {
        return (int) $this->data[self::MAX_DAYS];
    }

    /**
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->data[self::WEIGHT] = (float) $weight;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return (float) $this->data[self::WEIGHT];
    }

    /**
     * @param $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->data[self::PRICE] = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->data[self::PRICE];
    }

    /**
     * @param $isTaxable
     * @return $this
     */
    public function setIsTaxable($isTaxable)
    {
        $this->data[self::IS_TAXABLE] = (bool) $isTaxable;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsTaxable()
    {
        return (bool) $this->data[self::IS_TAXABLE];
    }

    /**
     * @param $isDiscountable
     * @return $this
     */
    public function setIsDiscountable($isDiscountable)
    {
        $this->data[self::IS_DISCOUNTABLE] = (bool) $isDiscountable;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDiscountable()
    {
        return (bool) $this->data[self::IS_DISCOUNTABLE];
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
        return $this->get(self::COMPANY) . '_' . $this->get(self::METHOD);
    }
}
