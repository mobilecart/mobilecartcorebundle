<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ContentSlotInsert
 * @package MobileCart\CoreBundle\EventListener\ContentSlot
 */
class ContentSlotInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param Event $event
     */
    public function onContentSlotInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);
        $event->setReturnData($returnData);
    }
}
