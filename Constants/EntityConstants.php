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
    // const BOOLEAN_YESNO = 'booleanyesno'; // todo: implement this as a yes/no select input with 2 rows in x_var_value_int

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

    const CONTENT_TYPE_IMAGE = 'image';
    const CONTENT_TYPE_EMBED = 'embed';
    const CONTENT_TYPE_HTML = 'html';

    const PRODUCT_TYPE_SIMPLE = 1;
    const PRODUCT_TYPE_CONFIGURABLE = 2;

    /**
     * @return array
     */
    static function getProductTypes()
    {
        return [
            self::PRODUCT_TYPE_SIMPLE => 'Simple',
            self::PRODUCT_TYPE_CONFIGURABLE => 'Configurable',
        ];
    }

    /**
     * @return array
     */
    static function getContentTypes()
    {
        return [
            self::CONTENT_TYPE_IMAGE => 'Image',
            self::CONTENT_TYPE_EMBED => 'Embed / Video',
            self::CONTENT_TYPE_HTML => 'Text / HTML',
        ];
    }

    const DISPLAY_PRODUCTS = 1;
    const DISPLAY_TEMPLATE = 2;
    const DISPLAY_TEMPLATE_PRODUCTS = 3;

    /**
     * @return array
     */
    static function getDisplayModes()
    {
        return [
            self::DISPLAY_PRODUCTS => 'Products only',
            self::DISPLAY_TEMPLATE => 'Template only',
            self::DISPLAY_TEMPLATE_PRODUCTS => 'Template and Products',
        ];
    }

    // Form inputs
    const INPUT_TEXT = 'text';
    const INPUT_NUMBER = 'number';
    const INPUT_DATE = 'date';
    const INPUT_SELECT = 'select';
    const INPUT_MULTISELECT = 'multiselect';
    const INPUT_CHECKBOX = 'checkbox';

    static $formInputs = [
        self::INPUT_TEXT => 'Text',
        self::INPUT_NUMBER => 'Number',
        self::INPUT_DATE => 'Date',
        self::INPUT_SELECT => 'Select',
        self::INPUT_MULTISELECT => 'Multiselect',
        self::INPUT_CHECKBOX => 'Checkbox',
    ];

    // Parent entities
    const ADMIN_USER = 'admin_user';
    const CART = 'cart';
    const CART_ITEM = 'cart_item';
    const CATEGORY = 'category';
    const CATEGORY_IMAGE = 'category_image';
    const CATEGORY_PRODUCT = 'category_product';
    const CONFIG_SETTING = 'config_setting';
    const CONTENT = 'content';
    const CONTENT_IMAGE = 'content_image';
    const CONTENT_SLOT = 'content_slot';
    const CUSTOMER = 'customer';
    const CUSTOMER_ADDRESS = 'customer_address';
    const CUSTOMER_GROUP = 'customer_group';
    const CUSTOMER_GROUP_PRODUCT_PRICE = 'customer_group_product_price';
    const CUSTOMER_TOKEN = 'customer_token';
    const DISCOUNT = 'discount';
    const OBJECT_LOG = 'object_log';
    const ORDER = 'order';
    const ORDER_ITEM = 'order_item';
    const ORDER_SHIPMENT = 'order_shipment';
    const ORDER_INVOICE = 'order_invoice';
    const ORDER_PAYMENT = 'order_payment';
    const ORDER_REFUND = 'order_refund';
    const ORDER_HISTORY = 'order_history';
    const PRODUCT = 'product';
    const PRODUCT_IMAGE = 'product_image';
    const PRODUCT_CONFIG = 'product_config';
    const PRODUCT_TIER_PRICE = 'product_tier_price';
    const REF_COUNTRY_REGION = 'ref_country_region';
    const SEARCH_TERM = 'search_term';
    const SHIPPING_METHOD = 'shipping_method';
    const URL_REWRITE = 'url_rewrite';
    const WEBHOOK_LOG = 'webhook_log';

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

    const SOURCE_ADDRESS_KEY = 'source_address_key';
    const CUSTOMER_ADDRESS_ID = 'customer_address_id';
}
