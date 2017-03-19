<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class OrderSearch
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderSearch
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
    public function onOrderSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

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
