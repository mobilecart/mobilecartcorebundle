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

class RateRequest extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'include_all' => 0,
            'to_array'    => 0,
            'postcode'    => '',
            'country_id'  => '',
            'region'      => '',
        ]);
    }
}
