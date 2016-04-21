<?php

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\Shipping\Rate;
use Symfony\Component\EventDispatcher\Event;

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
    public function __construct()
    {
        parent::__construct();
    }

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    /**
     * Get rates while filtering on criteria
     *
     * @param Event $event
     */
    public function onShippingRateCollect(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        // todo : check criteria ; load from db

        $event->addRate($this);

        $event->setReturnData($returnData);
    }
}
