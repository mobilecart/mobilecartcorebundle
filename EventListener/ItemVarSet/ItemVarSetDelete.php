<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSet;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ItemVarSetDelete
 * @package MobileCart\CoreBundle\EventListener\ItemVarSet
 */
class ItemVarSetDelete
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
    public function onItemVarSetDelete(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::ITEM_VAR_SET);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Custom Field Set Deleted!'
            );
        }

        $event->setReturnData($returnData);
    }
}
