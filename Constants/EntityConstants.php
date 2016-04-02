<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Constants;

class EntityConstants
{
    // EAV objects
    const ITEM_VAR = 'item_var';
    const ITEM_VAR_OPTION = 'item_var_option';
    const ITEM_VAR_OPTION_DATETIME = 'item_var_option_datetime';
    const ITEM_VAR_OPTION_DECIMAL = 'item_var_option_decimal';
    const ITEM_VAR_OPTION_INT = 'item_var_option_int';
    const ITEM_VAR_OPTION_VARCHAR = 'item_var_option_varchar';
    const ITEM_VAR_OPTION_TEXT = 'item_var_option_text';
    const ITEM_VAR_SET = 'item_var_set';
    const ITEM_VAR_SET_VAR = 'item_var_set_var';

    // Data types
    const DATETIME = 'datetime';
    const DECIMAL = 'decimal';
    const INT = 'int';
    const TEXT = 'text';
    const VARCHAR = 'varchar';
    const BOOLEAN = 'boolean';

    /**
     * @return array
     */
    static function getDatatypes()
    {
        return [
            self::DATETIME => 'Datetime',
            self::DECIMAL => 'Decimal',
            self::INT => 'Integer',
            self::TEXT => 'Text',
            self::VARCHAR => 'Varchar',
        ];
    }

    // Form inputs
    const INPUT_TEXT = 'text';
    const INPUT_NUMBER = 'number';
    const INPUT_DATE = 'date';
    const INPUT_SELECT = 'select';
    const INPUT_MULTISELECT = 'multiselect';

    static $formInputs = [
        self::INPUT_TEXT => 'Text',
        self::INPUT_NUMBER => 'Number',
        self::INPUT_DATE => 'Date',
        self::INPUT_SELECT => 'Select',
        self::INPUT_MULTISELECT => 'Multiselect',
    ];

    // Parent entities
    const ADMIN_USER = 'admin_user';
    const CART = 'cart';
    const CART_ITEM = 'cart_item';
    const CATEGORY = 'category';
    const CATEGORY_IMAGE = 'category_image';
    const CATEGORY_PRODUCT = 'category_product';
    const CONTENT = 'content';
    const CONTENT_IMAGE = 'content_image';
    const CUSTOMER = 'customer';
    const CUSTOMER_GROUP = 'customer_group';
    const CUSTOMER_TOKEN = 'customer_token';
    const DISCOUNT = 'discount';
    const ORDER = 'order';
    const ORDER_ITEM = 'order_item';
    const ORDER_SHIPMENT = 'order_shipment';
    const ORDER_INVOICE = 'order_invoice';
    const ORDER_PAYMENT = 'order_payment';
    const ORDER_REFUND = 'order_refund';
    const PRODUCT = 'product';
    const PRODUCT_IMAGE = 'product_image';
    const REF_COUNTRY_REGION = 'ref_country_region';
    const SHIPPING_METHOD = 'shipping_method';
    const URL_REWRITE = 'url_rewrite';

    // Url Rewrite actions
    const REWRITE_ACTION_LIST = 'list';
    const REWRITE_ACTION_VIEW = 'view';

    /**
     * @return array
     */
    static function getUrlRewriteObjects()
    {
        return [
            self::PRODUCT => 'Product',
            self::CONTENT => 'Content',
            self::CATEGORY => 'Category',
        ];
    }

    /**
     * @return array
     */
    static function getUrlRewriteActions()
    {
        return [
            self::REWRITE_ACTION_LIST => 'Listing',
            self::REWRITE_ACTION_VIEW => 'View Single',
        ];
    }

    /**
     * @return array
     */
    static function getEavObjects()
    {
        return [
            self::CATEGORY => 'Category',
            self::CONTENT => 'Content',
            self::CUSTOMER => 'Customer',
            self::ORDER => 'Order',
            self::PRODUCT => 'Product',
        ];
    }

    // Child entities
    const IMAGE = 'image';
    const VALUE_DATETIME = 'var_value_datetime';
    const VALUE_DECIMAL = 'var_value_decimal';
    const VALUE_INT = 'var_value_int';
    const VALUE_TEXT = 'var_value_text';
    const VALUE_VARCHAR = 'var_value_varchar';

    /**
     * @param $datatype
     * @return string
     */
    static function getVarDatatype($datatype)
    {
        $types = self::getVarDatatypes();
        return isset($types[$datatype])
            ? $types[$datatype]
            : '';
    }

    /**
     * @return array
     */
    static function getVarDatatypes()
    {
        return [
            'datetime' => self::VALUE_DATETIME,
            'decimal' => self::VALUE_DECIMAL,
            'int' => self::VALUE_INT,
            'text' => self::VALUE_TEXT,
            'varchar' => self::VALUE_VARCHAR,
        ];
    }
}
