<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use MobileCart\CoreBundle\Event\CoreEvent;

class ShippingMethodUpdate
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
    public function onShippingMethodUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\ShippingMethod $entity */
        $entity = $event->getEntity();
        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Shipping Method Updated !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Shipping Method');
        }
    }
}
