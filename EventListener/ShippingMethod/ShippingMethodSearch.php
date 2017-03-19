<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ShippingMethodSearch
 * @package MobileCart\CoreBundle\EventListener\ShippingMethod
 */
class ShippingMethodSearch
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function onShippingMethodSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->search();

        $event->setReturnData($returnData);
    }
}
