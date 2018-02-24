<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarOptionUpdate
 * @package MobileCart\CoreBundle\EventListener\ItemVarOption
 */
class ItemVarOptionUpdate
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
    public function onItemVarOptionUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $entity->setUrlValue($this->getEntityService()->slugify($entity->getUrlValue()));
        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Custom Field Option Updated!');
    }
}
