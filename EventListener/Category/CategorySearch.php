<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use Symfony\Component\EventDispatcher\Event;

class CategorySearch
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

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function onCategorySearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->addJoin('left', 'category_product', 'cp', 'category_id')
            ->addColumn('count(cp.product_id)', 'product_count')
            ->addGroupBy('main.id');

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        $event->setReturnData($returnData);
    }
}
