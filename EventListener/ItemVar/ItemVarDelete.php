<?php

namespace MobileCart\CoreBundle\EventListener\ItemVar;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ItemVarDelete
 * @package MobileCart\CoreBundle\EventListener\ItemVar
 */
class ItemVarDelete
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
    public function onItemVarDelete(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::ITEM_VAR);
        $event->addSuccessMessage('Custom Field Deleted !');
    }
}
