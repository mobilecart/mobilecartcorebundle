<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CustomerDelete
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerDelete
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
    public function onCustomerDelete(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::CUSTOMER);
        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Customer Deleted!'
            );
        }
    }
}
