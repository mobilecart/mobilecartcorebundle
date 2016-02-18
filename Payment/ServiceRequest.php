<?php

namespace MobileCart\CoreBundle\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class ServiceRequest extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([]);
    }
}
