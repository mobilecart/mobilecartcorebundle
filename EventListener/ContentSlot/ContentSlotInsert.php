<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ContentSlotInsert
 * @package MobileCart\CoreBundle\EventListener\ContentSlot
 */
class ContentSlotInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onContentSlotInsert(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Content Slot Created!');
    }
}
