<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Event;

final class CoreEvents
{
    const HOME_VIEW_RETURN = 'home.view.return';
    const DASHBOARD_VIEW_RETURN = 'dashboard.view.return';

    const LOGIN_SUCCESS = 'login.success';
    const LOGIN_LOCKED = 'login.locked';
    const LOGIN_VIEW_RETURN = 'login.view.return';
    const LOGOUT_SUCCESS = 'logout.success';

    const CART_ADD_PRODUCT = 'cart.add.product';
    const CART_UPDATE_TOTALS_SHIPPING = 'cart.update.totals.shipping';
    const CART_ADD_SHIPMENT = 'cart.add.shipment';
    const CART_UPDATE_MULTI_SHIPMENT = 'cart.update.multi.shipment';
    const CART_ADD_DISCOUNT = 'cart.add.discount';
    const CART_REMOVE_PRODUCT = 'cart.remove.product';
    const CART_REMOVE_PRODUCTS = 'cart.remove.products';
    const CART_VIEW_RETURN = 'cart.view.return';
    const CART_ORDER = 'cart.order';
    const CART_UPDATE = 'cart.update';
    const CART_TOTAL = 'cart.total';

    const CHECKOUT_FORM = 'checkout.form';
    const CHECKOUT_VIEW_RETURN = 'checkout.view.return';
    const CHECKOUT_BILLING_ADDRESS_VIEW_RETURN = 'checkout.billing.address.view.return';
    const CHECKOUT_SHIPPING_ADDRESS_VIEW_RETURN = 'checkout.shipping.address.view.return';
    const CHECKOUT_PAYMENT_METHODS_VIEW_RETURN = 'checkout.payment.methods.view.return';
    const CHECKOUT_UPDATE_BILLING_ADDRESS = 'checkout.update.billing.address';
    const CHECKOUT_UPDATE_SHIPPING_ADDRESS = 'checkout.update.shipping.address';
    const CHECKOUT_UPDATE_SHIPPING_METHOD = 'checkout.update.shipping.method';
    const CHECKOUT_TOTALS_DISCOUNTS = 'checkout.totals.discounts';
    const CHECKOUT_UPDATE_TOTALS_DISCOUNTS = 'checkout.update.totals.discounts';
    const CHECKOUT_ORDER_SUMMARY = 'checkout.summary';
    const CHECKOUT_UPDATE_PAYMENT_METHOD = 'checkout.update.payment.method';
    const CHECKOUT_CONFIRM_ORDER = 'checkout.confirm.order';
    const CHECKOUT_SUBMIT_ORDER = 'checkout.submit.order';
    const CHECKOUT_SUBMIT_ORDER_SUCCESS = 'checkout.submit.order.success';
    const CHECKOUT_SUCCESS_RETURN = 'checkout.success.return';

    const PRODUCT_LIST = 'product.list';
    const PRODUCT_SEARCH = 'product.search';
    const PRODUCT_UPDATE = 'product.update';
    const PRODUCT_INSERT = 'product.insert';
    const PRODUCT_DELETE = 'product.delete';
    const PRODUCT_DUPLICATE = 'product.duplicate';
    const PRODUCT_SAVE = 'product.save';
    const PRODUCT_EDIT_RETURN = 'product.edit.return';
    const PRODUCT_NEW_RETURN = 'product.new.return';
    const PRODUCT_VIEW_RETURN = 'product.view.return';
    const PRODUCT_CREATE_RETURN = 'product.create.return';
    const PRODUCT_UPDATE_RETURN = 'product.update.return';
    const PRODUCT_ADMIN_FORM = 'product.admin.form';
    const PRODUCT_ADDTOCART_FORM = 'product.addtocart.form';

    const CATEGORY_LIST = 'category.list';
    const CATEGORY_SEARCH = 'category.search';
    const CATEGORY_UPDATE = 'category.update';
    const CATEGORY_INSERT = 'category.insert';
    const CATEGORY_DELETE = 'category.delete';
    const CATEGORY_SAVE = 'category.save';
    const CATEGORY_EDIT_RETURN = 'category.edit.return';
    const CATEGORY_NEW_RETURN = 'category.new.return';
    const CATEGORY_VIEW_RETURN = 'category.view.return';
    const CATEGORY_CREATE_RETURN = 'category.create.return';
    const CATEGORY_UPDATE_RETURN = 'category.update.return';
    const CATEGORY_ADMIN_FORM = 'category.admin.form';

    const CONFIG_SETTING_LIST = 'config_setting.list';
    const CONFIG_SETTING_SEARCH = 'config_setting.search';
    const CONFIG_SETTING_UPDATE = 'config_setting.update';
    const CONFIG_SETTING_INSERT = 'config_setting.insert';
    const CONFIG_SETTING_DELETE = 'config_setting.delete';
    const CONFIG_SETTING_SAVE = 'config_setting.save';
    const CONFIG_SETTING_EDIT_RETURN = 'config_setting.edit.return';
    const CONFIG_SETTING_NEW_RETURN = 'config_setting.new.return';
    const CONFIG_SETTING_CREATE_RETURN = 'config_setting.create.return';
    const CONFIG_SETTING_UPDATE_RETURN = 'config_setting.update.return';
    const CONFIG_SETTING_ADMIN_FORM = 'config_setting.admin.form';

    const CONTACT_FORM = 'contact.form';
    const CONTACT_FORM_POST = 'contact.form.post';

    const CONTENT_LIST = 'content.list';
    const CONTENT_SEARCH = 'content.search';
    const CONTENT_UPDATE = 'content.update';
    const CONTENT_INSERT = 'content.insert';
    const CONTENT_DELETE = 'content.delete';
    const CONTENT_SAVE = 'content.save';
    const CONTENT_EDIT_RETURN = 'content.edit.return';
    const CONTENT_NEW_RETURN = 'content.new.return';
    const CONTENT_VIEW_RETURN = 'content.view.return';
    const CONTENT_CREATE_RETURN = 'content.create.return';
    const CONTENT_UPDATE_RETURN = 'content.update.return';
    const CONTENT_ADMIN_FORM = 'content.admin.form';

    const CONTENT_SLOT_LIST = 'content_slot.list';
    const CONTENT_SLOT_SEARCH = 'content_slot.search';
    const CONTENT_SLOT_UPDATE = 'content_slot.update';
    const CONTENT_SLOT_INSERT = 'content_slot.insert';
    const CONTENT_SLOT_DELETE = 'content_slot.delete';
    const CONTENT_SLOT_SAVE = 'content_slot.save';
    const CONTENT_SLOT_EDIT_RETURN = 'content_slot.edit.return';
    const CONTENT_SLOT_NEW_RETURN = 'content_slot.new.return';
    const CONTENT_SLOT_VIEW_RETURN = 'content_slot.view.return';
    const CONTENT_SLOT_CREATE_RETURN = 'content_slot.create.return';
    const CONTENT_SLOT_UPDATE_RETURN = 'content_slot.update.return';
    const CONTENT_SLOT_ADMIN_FORM = 'content_slot.admin.form';

    const CUSTOMER_LIST = 'customer.list';
    const CUSTOMER_SEARCH = 'customer.search';
    const CUSTOMER_UPDATE = 'customer.update';
    const CUSTOMER_INSERT = 'customer.insert';
    const CUSTOMER_DELETE = 'customer.delete';
    const CUSTOMER_SAVE = 'customer.save';
    const CUSTOMER_EDIT_RETURN = 'customer.edit.return';
    const CUSTOMER_NEW_RETURN = 'customer.new.return';
    const CUSTOMER_CREATE_RETURN = 'customer.create.return';
    const CUSTOMER_UPDATE_RETURN = 'customer.update.return';
    const CUSTOMER_ADMIN_FORM = 'customer.admin.form';
    const CUSTOMER_REGISTER = 'customer.register';
    const CUSTOMER_REGISTER_FORM = 'customer.register.form';
    const CUSTOMER_REGISTER_RETURN = 'customer.register.return';
    const CUSTOMER_REGISTER_POST_RETURN = 'customer.register.post.return';
    const CUSTOMER_REGISTER_CHECK_EMAIL_RETURN = 'customer.register.checkemail.return';
    const CUSTOMER_REGISTER_CONFIRM = 'customer.register.confirm';
    const CUSTOMER_REGISTER_CONFIRM_RETURN = 'customer.register.confirm.return';
    const CUSTOMER_PROFILE_RETURN = 'customer.profile.return';
    const CUSTOMER_PROFILE_POST_RETURN = 'customer.profile.post.return';
    const CUSTOMER_PROFILE_FORM = 'customer.profile.form';
    const CUSTOMER_FORGOT_PASSWORD = 'customer.forgotpassword';
    const CUSTOMER_FORGOT_PASSWORD_FORM = 'customer.forgotpassword.form';
    const CUSTOMER_FORGOT_PASSWORD_RETURN = 'customer.forgotpassword.return';
    const CUSTOMER_FORGOT_PASSWORD_SUCCESS = 'customer.forgotpassword.success';
    const CUSTOMER_FORGOT_PASSWORD_POST_RETURN = 'customer.forgotpassword.post.return';
    const CUSTOMER_ORDERS_RETURN = 'customer.orders.return';
    const CUSTOMER_ORDER_RETURN = 'customer.order.return';
    const CUSTOMER_NAVIGATION = 'customer.navigation';
    const CUSTOMER_UPDATE_PASSWORD = 'customer.updatepassword';
    const CUSTOMER_UPDATE_PASSWORD_FORM = 'customer.updatepassword.form';
    const CUSTOMER_UPDATE_PASSWORD_RETURN = 'customer.updatepassword.return';
    const CUSTOMER_UPDATE_PASSWORD_SUCCESS = 'customer.updatepassword.success';
    const CUSTOMER_UPDATE_PASSWORD_POST_RETURN = 'customer.updatepassword.post.return';

    const CUSTOMER_ADDRESS_LIST = 'customer_address.list';
    const CUSTOMER_ADDRESS_SEARCH = 'customer_address.search';
    const CUSTOMER_ADDRESS_UPDATE = 'customer_address.update';
    const CUSTOMER_ADDRESS_INSERT = 'customer_address.insert';
    const CUSTOMER_ADDRESS_DELETE = 'customer_address.delete';
    const CUSTOMER_ADDRESS_SAVE = 'customer_address.save';
    const CUSTOMER_ADDRESS_FORM = 'customer_address.form';
    const CUSTOMER_ADDRESS_EDIT_RETURN = 'customer_address.edit.return';
    const CUSTOMER_ADDRESS_NEW_RETURN = 'customer_address.new.return';
    const CUSTOMER_ADDRESS_CREATE_RETURN = 'customer_address.create.return';
    const CUSTOMER_ADDRESS_UPDATE_RETURN = 'customer_address.update.return';

    const DISCOUNT_LIST = 'discount.list';
    const DISCOUNT_SEARCH = 'discount.search';
    const DISCOUNT_UPDATE = 'discount.update';
    const DISCOUNT_INSERT = 'discount.insert';
    const DISCOUNT_DELETE = 'discount.delete';
    const DISCOUNT_SAVE = 'discount.save';
    const DISCOUNT_EDIT_RETURN = 'discount.edit.return';
    const DISCOUNT_NEW_RETURN = 'discount.new.return';
    const DISCOUNT_CREATE_RETURN = 'discount.create.return';
    const DISCOUNT_UPDATE_RETURN = 'discount.update.return';
    const DISCOUNT_ADMIN_FORM = 'discount.admin.form';

    const EXPORT_RUN = 'export.run';
    const EXPORT_RUN_RETURN = 'export.run.return';
    const EXPORT_OPTIONS_COLLECT = 'export_options.collect';
    const EXPORT_OPTIONS_VIEW_RETURN = 'export_options.view.return';

    const ITEM_VAR_LIST = 'item_var.list';
    const ITEM_VAR_SEARCH = 'item_var.search';
    const ITEM_VAR_UPDATE = 'item_var.update';
    const ITEM_VAR_INSERT = 'item_var.insert';
    const ITEM_VAR_DELETE = 'item_var.delete';
    const ITEM_VAR_SAVE = 'item_var.save';
    const ITEM_VAR_EDIT_RETURN = 'item_var.edit.return';
    const ITEM_VAR_NEW_RETURN = 'item_var.new.return';
    const ITEM_VAR_CREATE_RETURN = 'item_var.create.return';
    const ITEM_VAR_UPDATE_RETURN = 'item_var.update.return';
    const ITEM_VAR_ADMIN_FORM = 'item_var.admin.form';

    const ITEM_VAR_OPTION_LIST = 'item_var_option.list';
    const ITEM_VAR_OPTION_SEARCH = 'item_var_option.search';
    const ITEM_VAR_OPTION_UPDATE = 'item_var_option.update';
    const ITEM_VAR_OPTION_INSERT = 'item_var_option.insert';
    const ITEM_VAR_OPTION_DELETE = 'item_var_option.delete';
    const ITEM_VAR_OPTION_SAVE = 'item_var_option.save';
    const ITEM_VAR_OPTION_EDIT_RETURN = 'item_var_option.edit.return';
    const ITEM_VAR_OPTION_NEW_RETURN = 'item_var_option.new.return';
    const ITEM_VAR_OPTION_CREATE_RETURN = 'item_var_option.create.return';
    const ITEM_VAR_OPTION_UPDATE_RETURN = 'item_var_option.update.return';
    const ITEM_VAR_OPTION_ADMIN_FORM = 'item_var_option.admin.form';

    const ITEM_VAR_SET_LIST = 'item_var_set.list';
    const ITEM_VAR_SET_SEARCH = 'item_var_set.search';
    const ITEM_VAR_SET_UPDATE = 'item_var_set.update';
    const ITEM_VAR_SET_INSERT = 'item_var_set.insert';
    const ITEM_VAR_SET_DELETE = 'item_var_set.delete';
    const ITEM_VAR_SET_SAVE = 'item_var_set.save';
    const ITEM_VAR_SET_EDIT_RETURN = 'item_var_set.edit.return';
    const ITEM_VAR_SET_NEW_RETURN = 'item_var_set.new.return';
    const ITEM_VAR_SET_CREATE_RETURN = 'item_var_set.create.return';
    const ITEM_VAR_SET_UPDATE_RETURN = 'item_var_set.update.return';
    const ITEM_VAR_SET_ADMIN_FORM = 'item_var_set.admin.form';

    const ITEM_VAR_SET_VAR_LIST = 'item_var_set_var.list';
    const ITEM_VAR_SET_VAR_SEARCH = 'item_var_set_var.search';
    const ITEM_VAR_SET_VAR_UPDATE = 'item_var_set_var.update';
    const ITEM_VAR_SET_VAR_INSERT = 'item_var_set_var.insert';
    const ITEM_VAR_SET_VAR_DELETE = 'item_var_set_var.delete';
    const ITEM_VAR_SET_VAR_SAVE = 'item_var_set_var.save';
    const ITEM_VAR_SET_VAR_EDIT_RETURN = 'item_var_set_var.edit.return';
    const ITEM_VAR_SET_VAR_NEW_RETURN = 'item_var_set_var.new.return';
    const ITEM_VAR_SET_VAR_CREATE_RETURN = 'item_var_set_var.create.return';
    const ITEM_VAR_SET_VAR_UPDATE_RETURN = 'item_var_set_var.update.return';
    const ITEM_VAR_SET_VAR_ADMIN_FORM = 'item_var_set_var.admin.form';

    const MENU_BUILD = 'menu.build';

    const ORDER_LIST = 'order.list';
    const ORDER_SEARCH = 'order.search';
    const ORDER_UPDATE = 'order.update';
    const ORDER_UPDATE_SHIPPING = 'order.update.shipping';
    const ORDER_UPDATE_ITEMS = 'order.update.items';
    const ORDER_ADD_ITEM = 'order.add.item';
    const ORDER_REMOVE_ITEM = 'order.remove.item';
    const ORDER_ADD_DISCOUNT = 'order.add.discount';
    const ORDER_REMOVE_DISCOUNT = 'order.remove.discount';
    const ORDER_UPDATE_CUSTOMER = 'order.update.customer';
    const ORDER_INSERT = 'order.insert';
    const ORDER_DELETE = 'order.delete';
    const ORDER_EXPORT = 'order.export';
    const ORDER_SAVE = 'order.save';
    const ORDER_EDIT_RETURN = 'order.edit.return';
    const ORDER_NEW_RETURN = 'order.new.return';
    const ORDER_CREATE_RETURN = 'order.create.return';
    const ORDER_UPDATE_RETURN = 'order.update.return';
    const ORDER_ADMIN_FORM = 'order.admin.form';
    const ORDER_SUBMIT_SUCCESS = 'order.submit.success';

    const ORDER_ITEM_LIST = 'order_item.list';
    const ORDER_ITEM_SEARCH = 'order_item.search';

    const ORDER_PAYMENT_LIST = 'order_payment.list';
    const ORDER_PAYMENT_SEARCH = 'order_payment.search';
    const ORDER_PAYMENT_NEW_RETURN = 'order_payment.new.return';
    const ORDER_PAYMENT_INSERT = 'order_payment.insert';
    const ORDER_PAYMENT_CREATE_RETURN = 'order_payment.create.return';
    const ORDER_PAYMENT_EDIT_RETURN = 'order_payment.edit.return';
    const ORDER_PAYMENT_UPDATE = 'order_payment.update';
    const ORDER_PAYMENT_UPDATE_RETURN = 'order_payment.update.return';
    const ORDER_PAYMENT_ADMIN_FORM = 'order_payment.admin.form';

    const ORDER_SHIPMENT_LIST = 'order_shipment.list';
    const ORDER_SHIPMENT_SEARCH = 'order_shipment.search';
    const ORDER_SHIPMENT_NEW_RETURN = 'order_shipment.new.return';
    const ORDER_SHIPMENT_INSERT = 'order_shipment.insert';
    const ORDER_SHIPMENT_CREATE_RETURN = 'order_shipment.create.return';
    const ORDER_SHIPMENT_EDIT_RETURN = 'order_shipment.edit.return';
    const ORDER_SHIPMENT_UPDATE = 'order_shipment.update';
    const ORDER_SHIPMENT_UPDATE_RETURN = 'order_shipment.update.return';
    const ORDER_SHIPMENT_ADMIN_FORM = 'order_shipment.admin.form';

    // All Payment methods
    const PAYMENT_METHOD_COLLECT = 'payment_method.collect';

    // Payment methods, filtered by request
    const PAYMENT_SERVICE_COLLECT = 'payment_service.collect';

    // All Shipping methods
    const SHIPPING_METHOD_COLLECT = 'shipping_method.collect';

    // Shipping methods/rates, filtered by request
    const SHIPPING_RATE_COLLECT = 'shipping_rate.collect';

    const SHIPPING_METHOD_LIST = 'shipping_method.list';
    const SHIPPING_METHOD_SEARCH = 'shipping_method.search';
    const SHIPPING_METHOD_UPDATE = 'shipping_method.update';
    const SHIPPING_METHOD_INSERT = 'shipping_method.insert';
    const SHIPPING_METHOD_DELETE = 'shipping_method.delete';
    const SHIPPING_METHOD_SAVE = 'shipping_method.save';
    const SHIPPING_METHOD_EDIT_RETURN = 'shipping_method.edit.return';
    const SHIPPING_METHOD_UPDATE_RETURN = 'shipping_method.update.return';
    const SHIPPING_METHOD_NEW_RETURN = 'shipping_method.new.return';
    const SHIPPING_METHOD_CREATE_RETURN = 'shipping_method.create.return';
    const SHIPPING_METHOD_ADMIN_FORM = 'shipping_method.admin.form';

    const URL_REWRITE_LIST = 'url_rewrite.list';
    const URL_REWRITE_SEARCH = 'url_rewrite.search';
    const URL_REWRITE_UPDATE = 'url_rewrite.update';
    const URL_REWRITE_INSERT = 'url_rewrite.insert';
    const URL_REWRITE_DELETE = 'url_rewrite.delete';
    const URL_REWRITE_SAVE = 'url_rewrite.save';
    const URL_REWRITE_EDIT_RETURN = 'url_rewrite.edit.return';
    const URL_REWRITE_NEW_RETURN = 'url_rewrite.new.return';
    const URL_REWRITE_CREATE_RETURN = 'url_rewrite.create.return';
    const URL_REWRITE_UPDATE_RETURN = 'url_rewrite.update.return';
    const URL_REWRITE_ADMIN_FORM = 'url_rewrite.admin.form';

    const WEBHOOK_LOG_INSERT = 'webhook_log.insert';
    const WEBHOOK_LOG_UPDATE = 'webhook_log.update';
}
