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
        try {
            $this->getEntityService()->remove($entity, EntityConstants::CUSTOMER);
            $event->setSuccess(true);
            $event->addSuccessMessage('Customer Deleted !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while deleting the Customer');
        }
    }
}
