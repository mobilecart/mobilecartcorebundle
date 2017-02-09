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

class SourceAddress extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'key' => '',
            'label' => '',
            'street' => '',
            'city' => '',
            'province' => '',
            'postcode' => '',
            'country' => '',
        ]);
    }
}
