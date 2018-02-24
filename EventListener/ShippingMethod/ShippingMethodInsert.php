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
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);

        if ($entity && $event->getRequest()->getSession()) {

            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Shipping Method Created!'
            );
        }

        $event->setReturnData($returnData);
    }
}
