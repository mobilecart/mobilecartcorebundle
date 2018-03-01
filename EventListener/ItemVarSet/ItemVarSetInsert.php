<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSet;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarSetInsert
 * @package MobileCart\CoreBundle\EventListener\ItemVarSet
 */
class ItemVarSetInsert
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
    public function onItemVarSetInsert(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\ItemVarSet $entity */
        $entity = $event->getEntity();

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Custom Field Set Created !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Custom Field Set');
        }
    }
}
