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
 * Class Item
 * @package MobileCart\CoreBundle\CartComponent
 */
class Item extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const PARENT_OPTIONS = 'parent_options';
    const SKU = 'sku';
    const NAME = 'name';
    const SLUG = 'slug';
    const IMAGES = 'images';
    const CURRENCY = 'currency';
    const PRICE = 'price';
    const TAX = 'tax';
    const DISCOUNT = 'discount';
    const BASE_CURRENCY = 'base_currency';
    const BASE_PRICE = 'base_price';
    const BASE_TAX = 'base_tax';
    const BASE_DISCOUNT = 'base_discount';
    const TIER_PRICES = 'tier_prices';
    const QTY = 'qty';
    const MIN_QTY = 'min_qty';
    const AVAIL_QTY = 'avail_qty';
    const PROMO_QTY = 'promo_qty';
    const ORIG_QTY = 'orig_qty';
    const CATEGORY_IDS = 'category_ids';
    const CUSTOM = 'custom'; // for engravings, etc
    const WEIGHT = 'weight';
    const WEIGHT_UNIT = 'weight_unit';
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const LENGTH = 'length';
    const MEASURE_UNIT = 'measure_unit';
    const IS_TAXABLE = 'is_taxable';
    const IS_DISCOUNTABLE = 'is_discountable';
    const IS_ENABLED = 'is_enabled';
    const IS_IN_STOCK = 'is_in_stock';
    const IS_QTY_MANAGED = 'is_qty_managed';
    const IS_FLAT_SHIPPING = 'is_flat_shipping';
    const FLAT_SHIPPING_PRICE = 'flat_shipping_price';
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
            self::ID              => 0,
            self::PRODUCT_ID      => 0,
            self::PARENT_OPTIONS  => [],
            self::SKU             => '',
            self::NAME            => '',
            self::SLUG            => '',
            self::IMAGES          => [],
            self::CURRENCY        => '',
            self::PRICE           => 0,
            self::TAX             => 0,
            self::DISCOUNT        => 0,
            self::BASE_CURRENCY   => '',
            self::BASE_PRICE      => 0,
            self::BASE_TAX        => 0,
            self::BASE_DISCOUNT   => 0,
            self::TIER_PRICES     => [],
            self::QTY             => 0,
            self::MIN_QTY         => 0,
            self::AVAIL_QTY       => 0,
            self::PROMO_QTY       => 0,
            self::ORIG_QTY        => 0,
            self::CATEGORY_IDS    => [],
            self::CUSTOM          => '',
            self::WEIGHT          => 0,
            self::WEIGHT_UNIT     => 'lb',
            self::WIDTH           => 0,
            self::HEIGHT          => 0,
            self::LENGTH          => 0,
            self::MEASURE_UNIT    => 'in',
            self::IS_TAXABLE      => false,
            self::IS_DISCOUNTABLE => true,
            self::IS_ENABLED      => true,
            self::IS_IN_STOCK     => true,
            self::IS_QTY_MANAGED  => false,
            self::IS_FLAT_SHIPPING => false,
            self::FLAT_SHIPPING_PRICE => 0.0,
            self::CUSTOMER_ADDRESS_ID => 'main', // "main" shipping address info in customer table
            self::SOURCE_ADDRESS_KEY => 'main', // "main" address set on the cart.shipping service
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
                    case self::PRODUCT_ID:
                        $this->setProductId($value);
                        break;
                    case self::PARENT_OPTIONS:
                        if (is_object($value)) {
                            $value = (array) get_object_vars($value);
                        }
                        $this->setParentOptions($value);
                        break;
                    case self::SKU:
                        $this->setSku($value);
                        break;
                    case self::NAME:
                        $this->setName($value);
                        break;
                    case self::SLUG:
                        $this->setSlug($value);
                        break;
                    case self::IMAGES:
                        $this->setImages($value);
                        break;
                    case self::CURRENCY:
                        $this->setCurrency($value);
                        break;
                    case self::PRICE:
                        $this->setPrice($value);
                        break;
                    case self::TAX:
                        $this->setTax($value);
                        break;
                    case self::DISCOUNT:
                        $this->setDiscount($value);
                        break;
                    case self::BASE_CURRENCY:
                        $this->setBaseCurrency($value);
                        break;
                    case self::BASE_PRICE:
                        $this->setBasePrice($value);
                        break;
                    case self::BASE_TAX:
                        $this->setBaseTax($value);
                        break;
                    case self::BASE_DISCOUNT:
                        $this->setBaseDiscount($value);
                        break;
                    case self::TIER_PRICES:
                        // break them up and add them individually
                        $tierPrices = [];
                        if (is_array($value) && count($value)) {
                            foreach($value as $tierPriceData) {
                                if (is_array($tierPriceData)) {
                                    $tierPrices[] = new ArrayWrapper($tierPriceData);
                                } elseif ($tierPriceData instanceof ArrayWrapper) {
                                    $tierPrices[] = $tierPriceData;
                                } elseif (is_object($tierPriceData)) {
                                    $tierPrices[] = new ArrayWrapper(get_object_vars($tierPriceData));
                                }
                            }
                        }
                        $this->setTierPrices($tierPrices);
                        break;
                    case self::QTY:
                        $this->setQty($value);
                        break;
                    case self::MIN_QTY:
                        $this->setMinQty($value);
                        break;
                    case self::AVAIL_QTY:
                        $this->setAvailQty($value);
                        break;
                    case self::PROMO_QTY:
                        $this->setPromoQty($value);
                        break;
                    case self::ORIG_QTY:
                        $this->setOrigQty($value);
                        break;
                    case self::CATEGORY_IDS:
                        $this->setCategoryIds($value);
                        break;
                    case self::CUSTOM:
                        $this->setCustom($value);
                        break;
                    case self::WEIGHT:
                        $this->setWeight($value);
                        break;
                    case self::WEIGHT_UNIT:
                        $this->setWeightUnit($value);
                        break;
                    case self::WIDTH:
                        $this->setWidth($value);
                        break;
                    case self::HEIGHT:
                        $this->setHeight($value);
                        break;
                    case self::LENGTH:
                        $this->setLength($value);
                        break;
                    case self::MEASURE_UNIT:
                        $this->setMeasureUnit($value);
                        break;
                    case self::IS_TAXABLE:
                        $this->setIsTaxable($value);
                        break;
                    case self::IS_DISCOUNTABLE:
                        $this->setIsDiscountable($value);
                        break;
                    case self::IS_ENABLED:
                        $this->setIsEnabled($value);
                        break;
                    case self::IS_IN_STOCK:
                        $this->setIsInStock($value);
                        break;
                    case self::IS_QTY_MANAGED:
                        $this->setIsQtyManaged($value);
                        break;
                    case self::IS_FLAT_SHIPPING:
                        $this->setIsFlatShipping($value);
                        break;
                    case self::FLAT_SHIPPING_PRICE:
                        $this->setFlatShippingPrice($value);
                        break;
                    case self::CUSTOMER_ADDRESS_ID:
                        $this->setCustomerAddressId($value);
                        break;
                    case self::SOURCE_ADDRESS_KEY:
                        $this->setSourceAddressKey($value);
                        break;
                    default:
                        if (is_object($value)) {
                            $this->data[$key] = new ArrayWrapper(get_object_vars($value));
                        } else {
                            $this->data[$key] = $value;
                        }
                        break;
                }
            }
        }

        return $this;
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
     * Set the row id of the cart item
     *  note: this is not the product ID
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
     * Set the product ID
     *
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->data[self::PRODUCT_ID] = $productId;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->data[self::PRODUCT_ID];
    }

    /**
     * @param array $parentOptions
     * @return $this
     */
    public function setParentOptions(array $parentOptions)
    {
        $this->data[self::PARENT_OPTIONS] = $parentOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getParentOptions()
    {
        return $this->data[self::PARENT_OPTIONS];
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->data[self::SKU] = $sku;
        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->data[self::SKU];
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
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->data[self::SLUG] = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->data[self::SLUG];
    }

    /**
     * @param array $images
     * @return $this
     */
    public function setImages(array $images)
    {
        $this->data[self::IMAGES] = $images;
        return $this;
    }

    /**
     * @return array
     */
    public function getImages()
    {
        return $this->data[self::IMAGES];
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
     * @param $tax
     * @return $this
     */
    public function setTax($tax)
    {
        $this->data[self::TAX] = $tax;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->data[self::TAX];
    }

    /**
     * @param $discount
     * @return $this
     */
    public function setDiscount($discount)
    {
        $this->data[self::DISCOUNT] = $discount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return $this->data[self::DISCOUNT];
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setBaseCurrency($currency)
    {
        $this->data[self::BASE_CURRENCY] = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->data[self::BASE_CURRENCY];
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
     * @param $tax
     * @return $this
     */
    public function setBaseTax($tax)
    {
        $this->data[self::BASE_TAX] = $tax;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseTax()
    {
        return $this->data[self::BASE_TAX];
    }

    /**
     * @param $discount
     * @return $this
     */
    public function setBaseDiscount($discount)
    {
        $this->data[self::BASE_DISCOUNT] = $discount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseDiscount()
    {
        return $this->data[self::BASE_DISCOUNT];
    }

    /**
     * @param array $tierPrices
     * @return $this
     */
    public function setTierPrices(array $tierPrices)
    {
        $this->data[self::TIER_PRICES] = $tierPrices;
        return $this;
    }

    /**
     * @return array
     */
    public function getTierPrices()
    {
        return $this->data[self::TIER_PRICES];
    }

    /**
     * @param int $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->data[self::QTY] = (int) $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return (int) $this->data[self::QTY];
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setMinQty($qty)
    {
        $this->data[self::MIN_QTY] = (int) $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinQty()
    {
        return (int) $this->data[self::MIN_QTY];
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setAvailQty($qty)
    {
        $this->data[self::AVAIL_QTY] = (int) $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getAvailQty()
    {
        return (int) $this->data[self::AVAIL_QTY];
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setPromoQty($qty)
    {
        $this->data[self::PROMO_QTY] = (int) $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getPromoQty()
    {
        return (int) $this->data[self::PROMO_QTY];
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setOrigQty($qty)
    {
        $this->data[self::ORIG_QTY] = (int) $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrigQty()
    {
        return (int) $this->data[self::ORIG_QTY];
    }

    /**
     * @param $categoryIds
     * @return $this
     */
    public function setCategoryIds($categoryIds)
    {
        $this->data[self::CATEGORY_IDS] = $categoryIds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryIds()
    {
        return $this->data[self::CATEGORY_IDS];
    }

    /**
     * @param $custom
     * @return $this
     */
    public function setCustom($custom)
    {
        $this->data[self::CUSTOM] = $custom;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustom()
    {
        return $this->data[self::CUSTOM];
    }

    /**
     * @param $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->data[self::WEIGHT] = $weight;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->data[self::WEIGHT];
    }

    /**
     * @param $weightUnit
     * @return $this
     */
    public function setWeightUnit($weightUnit)
    {
        $this->data[self::WEIGHT_UNIT] = $weightUnit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWeightUnit()
    {
        return $this->data[self::WEIGHT_UNIT];
    }

    /**
     * @param $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->data[self::WIDTH] = $width;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->data[self::WIDTH];
    }

    /**
     * @param $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->data[self::HEIGHT] = $height;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->data[self::HEIGHT];
    }

    /**
     * @param $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->data[self::LENGTH] = $length;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->data[self::LENGTH];
    }

    /**
     * @param $measureUnit
     * @return $this
     */
    public function setMeasureUnit($measureUnit)
    {
        $this->data[self::MEASURE_UNIT] = $measureUnit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMeasureUnit()
    {
        return $this->data[self::MEASURE_UNIT];
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
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->data[self::IS_ENABLED] = (bool) $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return (bool) $this->data[self::IS_ENABLED];
    }

    /**
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock)
    {
        $this->data[self::IS_IN_STOCK] = (bool) $isInStock;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsInStock()
    {
        return (bool) $this->data[self::IS_IN_STOCK];
    }

    /**
     * @param $isQtyManaged
     * @return $this
     */
    public function setIsQtyManaged($isQtyManaged)
    {
        $this->data[self::IS_QTY_MANAGED] = (bool) $isQtyManaged;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsQtyManaged()
    {
        return (bool) $this->data[self::IS_QTY_MANAGED];
    }

    /**
     * @param bool $isFlatShipping
     * @return $this
     */
    public function setIsFlatShipping($isFlatShipping)
    {
        $this->data[self::IS_FLAT_SHIPPING] = (bool) $isFlatShipping;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFlatShipping()
    {
        return (bool) $this->data[self::IS_FLAT_SHIPPING];
    }

    /**
     * @param $flatShippingPrice
     * @return $this
     */
    public function setFlatShippingPrice($flatShippingPrice)
    {
        $this->data[self::FLAT_SHIPPING_PRICE] = $flatShippingPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFlatShippingPrice()
    {
        return $this->data[self::FLAT_SHIPPING_PRICE];
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
     * @param string $default
     * @return mixed
     */
    public function getCustomerAddressId($default = 'main')
    {
        $value = $this->data[self::CUSTOMER_ADDRESS_ID];
        if ($value) {
            return $value;
        }
        return $default;
    }

    /**
     * @param $sourceAddressKey
     * @return $this
     */
    public function setSourceAddressKey($sourceAddressKey)
    {
        $this->data[self::SOURCE_ADDRESS_KEY] = $sourceAddressKey;
        return $this;
    }

    /**
     * @param string $default
     * @return mixed
     */
    public function getSourceAddressKey($default = 'main')
    {
        $value = $this->data[self::SOURCE_ADDRESS_KEY];
        if ($value) {
            return $value;
        }
        return $default;
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
        try {
            return $condition->isValid();
        } catch(\Exception $e) {
            return false;
        }
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
     * @param array $errors
     * @return bool
     */
    public function meetsBasicCriteria(array &$errors = [])
    {
        if (!$this->getIsEnabled()) {
            $errors[] = "Product is not enabled : {$this->getSku()}";
            return false;
        }

        if (!$this->getIsInStock()) {
            $errors[] = "Product is not in stock : {$this->getSku()}";
            return false;
        }

        if ($this->get('promo_qty', 0) > 0 && $this->getQty() !== $this->get('promo_qty')) {
            $errors[] = 'Cannot change qty on promo item';
            return false;
        }

        return true;
    }

    /**
     * @param array $errors
     * @return bool
     */
    public function meetsMinMaxCriteria(array &$errors = [])
    {
        $minQty = $this->getMinQty();
        $availQty = $this->getAvailQty();
        $isQtyManaged = $this->getIsQtyManaged();

        $qty = $this->getQty();

        $minQtyMet = $minQty == 0 || ($minQty > 0 && $qty >= $minQty);
        $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $qty < $availQty);

        if (!$minQtyMet) {
            $errors[] = "Minimum Qty is not met : {$this->getSku()}, Qty: {$this->getMinQty()}";
            return false;
        }

        if (!$maxQtyMet) {
            $errors[] = "Insufficient stock level : {$this->getSku()}, Available: {$this->getAvailQty()}";
            return false;
        }

        return true;
    }
}
