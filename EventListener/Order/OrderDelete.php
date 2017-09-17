<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class OrderDelete
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderDelete
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
     * @param CoreEvent $event
     */
    public function onOrderDelete(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::ORDER);
        $event->addSuccessMessage('Order Deleted!');
    }
}
