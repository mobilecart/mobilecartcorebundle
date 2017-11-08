<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

/**
 * Class RateRequest
 * @package MobileCart\CoreBundle\Shipping
 */
class RateRequest extends ArrayWrapper
{
    const DEST_POSTCODE = 'dest_postcode';
    const DEST_COUNTRY_ID = 'dest_country_id';
    const DEST_REGION = 'dest_region';
    const SRC_POSTCODE = 'src_postcode';
    const SRC_COUNTRY_ID = 'src_country_id';
    const SRC_REGION = 'src_region';
    const SOURCE_ADDRESS_KEY = 'source_address_key';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
    const CART_ITEMS = 'cart_items';
    const PRODUCT_IDS = 'product_ids';
    const ADDTL_PRICE = 'addtl_price';
    const SKUS = 'skus';

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
            self::DEST_POSTCODE       => '',
            self::DEST_COUNTRY_ID     => '',
            self::DEST_REGION         => '',
            self::SRC_POSTCODE        => '',
            self::SRC_COUNTRY_ID      => '',
            self::SRC_REGION          => '',
            self::SOURCE_ADDRESS_KEY  => 'main',
            self::CUSTOMER_ADDRESS_ID => 0,
            self::CART_ITEMS          => [],
            self::PRODUCT_IDS         => [],
            self::SKUS                => [],
            self::ADDTL_PRICE         => 0,
        ];
    }

    /**
     * @param string $destPostcode
     * @return $this
     */
    public function setDestPostcode($destPostcode)
    {
        $this->data[self::DEST_POSTCODE] = $destPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestPostcode()
    {
        return $this->data[self::DEST_POSTCODE];
    }

    /**
     * @param string $destCountryId
     * @return $this
     */
    public function setDestCountryId($destCountryId)
    {
        $this->data[self::DEST_COUNTRY_ID] = $destCountryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestCountryId()
    {
        return $this->data[self::DEST_COUNTRY_ID];
    }

    /**
     * @param string $destRegion
     * @return $this
     */
    public function setDestRegion($destRegion)
    {
        $this->data[self::DEST_REGION] = $destRegion;
        return $this;
    }

    /**
     * @return string
     */
    public function getDestRegion()
    {
        return $this->data[self::DEST_REGION];
    }

    /**
     * @param string $srcPostcode
     * @return $this
     */
    public function setSrcPostcode($srcPostcode)
    {
        $this->data[self::SRC_POSTCODE] = $srcPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSrcPostcode()
    {
        return $this->data[self::SRC_POSTCODE];
    }

    /**
     * @param string $srcCountryId
     * @return $this
     */
    public function setSrcCountryId($srcCountryId)
    {
        $this->data[self::SRC_COUNTRY_ID] = $srcCountryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSrcCountryId()
    {
        return $this->data[self::SRC_COUNTRY_ID];
    }

    /**
     * @param string $srcRegion
     * @return $this
     */
    public function setSrcRegion($srcRegion)
    {
        $this->data[self::SRC_REGION] = $srcRegion;
        return $this;
    }

    /**
     * @return string
     */
    public function getSrcRegion()
    {
        return $this->data[self::SRC_REGION];
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
     * @param array $cartItems
     * @return $this
     */
    public function setCartItems(array $cartItems)
    {
        $this->data[self::CART_ITEMS] = $cartItems;
        return $this;
    }

    /**
     * @return array|\MobileCart\CoreBundle\CartComponent\Item[]
     */
    public function getCartItems()
    {
        return $this->data[self::CART_ITEMS];
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
     * @param $addtlPrice
     * @return $this
     */
    public function setAddtlPrice($addtlPrice)
    {
        $this->data[self::ADDTL_PRICE] = $addtlPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddtlPrice()
    {
        return $this->data[self::ADDTL_PRICE];
    }
}
