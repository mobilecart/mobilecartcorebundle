<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\EventDispatcher\Event;

class OrderSearch
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

    public function onOrderSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest());

        if (!$event->getRequest()->get($event->getSearch()->getSortDirParam(), '')) {
            $event->getSearch()->setSortDir('desc'); // sort by newest first, by default
        }

        $event->getSearch()->search();

        $event->setReturnData($returnData);
    }
}
