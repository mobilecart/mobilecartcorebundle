<?php

namespace MobileCart\CoreBundle\EventListener\ItemVar;

use Symfony\Component\EventDispatcher\Event;

class ItemVarSearch
{
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

    public function onItemVarSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->search();

        $event->setReturnData($returnData);
    }
}
