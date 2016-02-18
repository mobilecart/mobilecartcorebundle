<?php

namespace MobileCart\CoreBundle\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class ServiceResponse extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'success' => 0,
            'message' => '',
            'gateway_response' => null,
        ]);
    }
}
