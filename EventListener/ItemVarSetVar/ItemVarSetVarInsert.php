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
        /** @var \MobileCart\CoreBundle\Entity\ItemVarSetVar $entity */
        $entity = $event->getEntity();

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Field Mapping Created !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Field Mapping');
        }
    }
}
