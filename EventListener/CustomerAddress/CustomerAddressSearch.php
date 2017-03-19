<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CustomerAddressSearch
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressSearch
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
    public function onCustomerAddressSearch(Event $event)
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
