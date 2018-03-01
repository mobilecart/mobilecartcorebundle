<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ShippingMethodInsert
 * @package MobileCart\CoreBundle\EventListener\ShippingMethod
 */
class ShippingMethodInsert
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
    public function onShippingMethodInsert(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\ShippingMethod $entity */
        $entity = $event->getEntity();
        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Shipping Method Created !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Shipping Method');
        }
    }
}
