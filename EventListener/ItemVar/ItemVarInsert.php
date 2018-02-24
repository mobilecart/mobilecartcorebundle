<?php

namespace MobileCart\CoreBundle\EventListener\ItemVar;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarInsert
 * @package MobileCart\CoreBundle\EventListener\ItemVar
 */
class ItemVarInsert
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
    public function onItemVarInsert(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $entity->setUrlToken($this->getEntityService()->slugify($entity->getUrlToken()));
        $entity->setCode($this->getEntityService()->slugify($entity->getCode()));
        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Custom Field Created!');
    }
}
