<?php

namespace MobileCart\CoreBundle\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class Payment extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'is_successful' => 0,
            'code' => '',
            'label' => '',
            'base_currency' => '',
            'base_amount' => 0,
            'currency' => '',
            'amount' => 0,
            'confirmation' => '',
            'is_refund' => 0,
        ]);
    }
}
