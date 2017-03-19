<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSetVar;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ItemVarSetVarSearch
 * @package MobileCart\CoreBundle\EventListener\ItemVarSetVar
 */
class ItemVarSetVarSearch
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
    public function onItemVarSetVarSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->addJoin('inner', 'item_var', 'id', 'item_var_id')
            ->addColumn('item_var.name', 'item_var_name')
            ->addJoin('inner', 'item_var_set', 'id', 'item_var_set_id')
            ->addColumn('item_var_set.name', 'item_var_set_name')
            ->search();

        $event->setReturnData($returnData);
    }
}
