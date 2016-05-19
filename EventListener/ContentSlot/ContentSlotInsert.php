<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ContentSlotInsert
{
    protected $entityService;

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

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function onContentSlotInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        //$request = $event->getRequest();
        $entity = $event->getEntity();
        //$formData = $event->getFormData();

        $this->getEntityService()->persist($entity);

        $event->setReturnData($returnData);
    }
}
