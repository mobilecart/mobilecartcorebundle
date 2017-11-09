<?php

namespace MobileCart\CoreBundle\Event\Shipping;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class FilterShippingRateEvent
 * @package MobileCart\CoreBundle\Event\Shipping
 */
class FilterShippingRateEvent extends Event
{
    /**
     * @var array
     */
    protected $rates = []; // r[company_method] = Rate object

    /**
     * @var \MobileCart\CoreBundle\Shipping\RateRequest
     */
    protected $rateRequest;

    /**
     * @param \MobileCart\CoreBundle\Shipping\Rate $rate
     * @return $this
     */
    public function addRate(\MobileCart\CoreBundle\Shipping\Rate $rate)
    {
        $this->rates[$rate->getCode()] = $rate;
        return $this;
    }

    /**
     * @return array|\MobileCart\CoreBundle\Shipping\Rate[]
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param \MobileCart\CoreBundle\Shipping\RateRequest $rateRequest
     * @return $this
     */
    public function setRateRequest(\MobileCart\CoreBundle\Shipping\RateRequest $rateRequest)
    {
        $this->rateRequest = $rateRequest;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Shipping\RateRequest
     */
    public function getRateRequest()
    {
        return $this->rateRequest;
    }
}
