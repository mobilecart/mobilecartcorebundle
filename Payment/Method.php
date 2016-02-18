<?php

namespace MobileCart\CoreBundle\Payment;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class Method extends ArrayWrapper
{
    public function __construct()
    {
        parent::__construct([
            'code'        => '',
            'label'       => '',
            'is_frontend' => 0,
            'is_backend'  => 1,
        ]);
    }

    /**
     * @param array $values
     * @return string
     */
    public function getForm($values = [])
    {
        return '';
    }
}
