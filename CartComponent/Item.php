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
    const SKU = 'sku';
    const NAME = 'name';
    const SLUG = 'slug';
    const IMAGE = 'image';
    const PRICE = 'price';
    const QTY = 'qty';
    const CATEGORY_IDS = 'category_ids';
    const CUSTOM = 'custom'; // for engravings, etc
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
            self::ID              => 0,
            self::PRODUCT_ID      => 0,
            self::SKU             => '',
            self::NAME            => '',
            self::SLUG            => '',
            self::IMAGE           => '',
            self::PRICE           => 0,
            self::QTY             => 0,
            self::CATEGORY_IDS    => [],
            self::CUSTOM          => '',
            self::IS_TAXABLE      => false,
            self::IS_DISCOUNTABLE => true,
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
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->data[self::IMAGE] = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->data[self::IMAGE];
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
     * @param $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->data[self::QTY] = $qty;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->data[self::QTY];
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
