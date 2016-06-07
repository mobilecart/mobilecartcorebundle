<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Event\CoreEvent;

class ProductSearch
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

    public function onProductSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();

        // custom logic . tweak as needed
        $loadVarValues = 0;

        $categoryId = $event->getCategory()
            ? $event->getCategory()->getId()
            : 0;

        $filters = [];
        switch($event->getSection()) {
            case CoreEvent::SECTION_FRONTEND:

                $filters = [
                    'is_in_stock' => 1,
                    'is_public' => 1,
                    //'visibility' => 4,
                    'is_enabled' => 1,
                ];

                break;
            case CoreEvent::SECTION_BACKEND:
                //$loadVarValues = 1;
                break;
            case CoreEvent::SECTION_API:
                $loadVarValues = 1;
                break;
            default:

                break;
        }

        if ($request->get('format', '') == 'json') {
            $loadVarValues = 1;
        }

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->setCategoryId($categoryId)
            ->setPopulateVarValues($loadVarValues)
            ->parseRequest($event->getRequest())
            ->addFilters($filters);

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        if ($event->getCategory()) {
            $returnData['category'] = $event->getCategory();
        }

        $event->setReturnData($returnData);
    }
}
