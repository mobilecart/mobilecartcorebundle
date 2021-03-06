<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarOptionInsert
 * @package MobileCart\CoreBundle\EventListener\ItemVarOption
 */
class ItemVarOptionInsert
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
    public function onItemVarOptionInsert(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\ItemVarOptionInterface $entity */
        $entity = $event->getEntity();
        $entity->setUrlValue($this->getEntityService()->slugify($entity->getUrlValue()));

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Custom Field Option Created!');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Custom Field Option');
        }
    }
}
