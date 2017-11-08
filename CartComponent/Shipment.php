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
    const BASE_PRICE = 'base_price';
    const PRODUCT_IDS = 'product_ids';
    const SKUS = 'skus';
    const IS_TAXABLE = 'is_taxable';
    const IS_DISCOUNTABLE = 'is_discountable';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    const SOURCE_ADDRESS_KEY = 'source_address_key';

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
            self::ID                  => 0,
            self::SHIPPING_METHOD_ID  => 0,
            self::COMPANY             => '',
            self::METHOD              => '',
            self::CODE                => '',
            self::MIN_DAYS            => 0,
            self::MAX_DAYS            => 0,
            self::WEIGHT              => 0,
            self::PRICE               => 0,
            self::BASE_PRICE          => 0,
            self::PRODUCT_IDS         => [],
            self::SKUS                => [],
            self::IS_TAXABLE          => false,
            self::IS_DISCOUNTABLE     => false,
            self::CUSTOMER_ADDRESS_ID => 0,
            self::SOURCE_ADDRESS_KEY  => '',
        ];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        if ($data) {
            foreach($data as $key => $value) {
                switch($key) {
                    case self::ID:
                        $this->setId($value);
                        break;
                    case self::SHIPPING_METHOD_ID:
                        $this->setShippingMethodId($value);
                        break;
                    case self::COMPANY:
                        $this->setCompany($value);
                        break;
                    case self::METHOD:
                        $this->setMethod($value);
                        break;
                    case self::CODE:
                        // skip it, since it has its own getter
                        break;
                    case self::MIN_DAYS:
                        $this->setMinDays($value);
                        break;
                    case self::MAX_DAYS:
                        $this->setMaxDays($value);
                        break;
                    case self::WEIGHT:
                        $this->setWeight($value);
                        break;
                    case self::PRICE:
                        $this->setPrice($value);
                        break;
                    case self::BASE_PRICE:
                        $this->setBasePrice($value);
                        break;
                    case self::PRODUCT_IDS:
                        $this->setProductIds($value);
                        break;
                    case self::SKUS:
                        $this->setSkus($value);
                        break;
                    case self::IS_TAXABLE:
                        $this->setIsTaxable($value);
                        break;
                    case self::IS_DISCOUNTABLE:
                        $this->setIsDiscountable($value);
                        break;
                    case self::CUSTOMER_ADDRESS_ID:
                        $this->setCustomerAddressId($value);
                        break;
                    case self::SOURCE_ADDRESS_KEY:
                        $this->setSourceAddressKey($value);
                        break;
                    default:
                        if ($value instanceof \stdClass || is_object($value)) {
                            $this->data[$key] = new ArrayWrapper(get_object_vars($value));
                        } elseif (is_scalar($value) || is_array($value)) {
                            $this->data[$key] = $value;
                        }
                        break;
                }
            }
        }
        return $this;
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
     * @param $price
     * @return $this
     */
    public function setBasePrice($price)
    {
        $this->data[self::BASE_PRICE] = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePrice()
    {
        return $this->data[self::BASE_PRICE];
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function setProductIds(array $productIds)
    {
        $this->data[self::PRODUCT_IDS] = $productIds;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->data[self::PRODUCT_IDS];
    }

    /**
     * @param array $skus
     * @return $this
     */
    public function setSkus(array $skus)
    {
        $this->data[self::SKUS] = $skus;
        return $this;
    }

    /**
     * @return array
     */
    public function getSkus()
    {
        return $this->data[self::SKUS];
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
     * @param $customerAddressId
     * @return $this
     */
    public function setCustomerAddressId($customerAddressId)
    {
        $this->data[self::CUSTOMER_ADDRESS_ID] = $customerAddressId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddressId()
    {
        return $this->data[self::CUSTOMER_ADDRESS_ID];
    }

    /**
     * @param string $sourceAddressKey
     * @return $this
     */
    public function setSourceAddressKey($sourceAddressKey)
    {
        $this->data[self::SOURCE_ADDRESS_KEY] = $sourceAddressKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceAddressKey()
    {
        return $this->data[self::SOURCE_ADDRESS_KEY];
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
        switch($condition->getEntityField()) {
            case 'code':
                $condition->setSourceValue($this->getCode());
                break;
            default:
                $condition->setSourceValue($this->get($condition->getEntityField()));
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
