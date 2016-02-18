<?php

namespace MobileCart\CoreBundle\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class GatewayResponse extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'request' => '',
            'response' => '',
        ]);
    }
}
