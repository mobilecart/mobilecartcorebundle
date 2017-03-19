<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSet;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ItemVarSetSearch
 * @package MobileCart\CoreBundle\EventListener\ItemVarSet
 */
class ItemVarSetSearch
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
    public function onItemVarSetSearch(Event $event)
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
