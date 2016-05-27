<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSetVar;

use Symfony\Component\EventDispatcher\Event;

class ItemVarSetVarSearch
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

    public function onItemVarSetVarSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        // todo : get table names from EntityService

        $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->addJoin('inner', 'item_var', 'iiv', 'id', 'item_var_id')
            ->addColumn('iiv.name', 'item_var_name')
            ->addJoin('inner', 'item_var_set', 'iivs', 'id', 'item_var_set_id')
            ->addColumn('iivs.name', 'item_var_set_name')
            ->search();

        $event->setReturnData($returnData);
    }
}
