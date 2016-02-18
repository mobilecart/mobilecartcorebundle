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

class CheckoutConstants
{
    const STEP_BILLING_ADDRESS = 'billing_address';

    const STEP_SHIPPING_ADDRESS = 'shipping_address';

    const STEP_SHIPPING_METHOD = 'shipping_method';

    const STEP_TOTALS_DISCOUNTS = 'totals_discounts';

    const STEP_PAYMENT_METHODS = 'payment_methods';

    const STEP_SUMMARY_CONFIRM = 'summary_confirm';

    const STEP_SUBMIT_ORDER = 'submit_order';
}
