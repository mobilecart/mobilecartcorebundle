<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerForgotPassword
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerForgotPassword
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
    public function onCustomerForgotPassword(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Customer $entity */
        $entity = $event->getEntity();
        $confirmHash = md5(microtime());
        $entity->setConfirmHash($confirmHash);

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while updating account');
        }
    }
}
