<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ItemVarOptionSearch
 * @package MobileCart\CoreBundle\EventListener\ItemVarOption
 */
class ItemVarOptionSearch
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
    public function onItemVarOptionSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->addJoin('inner', 'item_var', 'id', 'item_var_id')
            ->addColumn('item_var.name', 'item_var_name');

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        $event->setReturnData($returnData);

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_item_var_option', $request->getQueryString());
        }
    }
}
