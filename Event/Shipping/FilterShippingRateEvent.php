<?php

namespace MobileCart\CoreBundle\Event\Shipping;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Event\CoreEvent;

class FilterShippingRateEvent extends CoreEvent
{
    /**
     * @var array
     */
    protected $rates = []; // r[company_method] = Rate object

    /**
     * @param $rate
     * @return $this
     */
    public function addRate($rate)
    {
        $this->rates[$rate->getCode()] = $rate;
        return $this;
    }

    /**
     * @return array
     */
    public function getRates()
    {
        $asArray = $this->getRateRequest()->get('to_array');
        if (!$asArray || !$this->rates) {
            return $this->rates;
        }

        $rates = [];
        foreach($this->rates as $code => $rate) {
            $data = $rate->toArray();
            $data['code'] = $code;
            /*
            if (!isset($data['id'])) {
                $data['id'] = $code;
            } //*/
            $rates[] = $data;
        }
        
        return $rates;
    }
}
