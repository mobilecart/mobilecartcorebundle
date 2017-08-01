<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class DiscountUpdate
 * @package MobileCart\CoreBundle\EventListener\Discount
 */
class DiscountUpdate
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
    public function onDiscountUpdate(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);

        if ($entity && $event->getRequest()->getSession()) {

            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Discount Updated!'
            );
        }

        $event->setReturnData($returnData);
    }
}
