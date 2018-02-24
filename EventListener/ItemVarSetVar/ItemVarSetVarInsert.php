<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSetVar;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarSetVarInsert
 * @package MobileCart\CoreBundle\EventListener\ItemVarSetVar
 */
class ItemVarSetVarInsert
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
    public function onItemVarSetVarInsert(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Field Mapping Created!');
    }
}
