<?php

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\Event\Shipping\FilterShippingRateEvent;

/**
 * Class FlatRate
 * @package MobileCart\CoreBundle\EventListener\Shipping
 *
 * This is a basic Shipping Rate
 *  the price is set in the service configuration via the magic setter in Rate
 *  and can be changed in the admin; along with cart pre-conditions
 */
class FlatRate extends Rate
{
    /**
     * @var bool
     */
    protected $isEnabled = true;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = (bool) $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return (bool) $this->isEnabled;
    }

    /**
     * Get rates while filtering on criteria
     *
     * @param FilterShippingRateEvent $event
     */
    public function onShippingRateCollect(FilterShippingRateEvent $event)
    {
        if ($this->getIsEnabled()) {
            /** @var \MobileCart\CoreBundle\Shipping\RateRequest $rateRequest */
            $rateRequest = $event->getRateRequest();
            $this->setProductIds($rateRequest->getProductIds());
            $this->setSkus($rateRequest->getSkus());
            $event->addRate($this);
        }
    }
}
