<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ContentSlotSearch
 * @package MobileCart\CoreBundle\EventListener\ContentSlot
 */
class ContentSlotSearch
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
    public function onContentSlotSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest());

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        $event->setReturnData($returnData);
    }
}
