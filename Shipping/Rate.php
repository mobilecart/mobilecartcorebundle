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

class Rate extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'currency' => 'USD',
            'price' => '',
            'cost' => '',
            'handling_cost' => '',
            'company' => '',
            'method' => '',
            'title' => '',
            'sort' => 1,
        ]);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getCompany() . '_' . $this->getMethod();
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['code'] = $this->getCode();
        return $data;
    }
}
