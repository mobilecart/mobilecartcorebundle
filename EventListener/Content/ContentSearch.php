<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Event\CoreEvent;

class ContentSearch
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

    public function onContentSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $filters = [];
        switch($event->getSection()) {
            case CoreEvent::SECTION_FRONTEND:

                $filters = [
                    'is_public' => 1,
                ];

                break;
            case CoreEvent::SECTION_BACKEND:
                // no-op
                break;
            case CoreEvent::SECTION_API:
                // no-op
                break;
            default:

                break;
        }

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->addFilters($filters)
        ;

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();
        $search->getEntityService()->populateChildData(EntityConstants::CONTENT_SLOT, $returnData['result']['entities']);

        $event->setReturnData($returnData);
    }
}
