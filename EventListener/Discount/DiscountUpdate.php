<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Discount;

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
        $entity = $event->getEntity();
        if ($entity->getAppliedTo() != Discount::$toSpecified) {
            $entity->setPreConditions('{}');
            $entity->setTargetConditions('{}');
        }
        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Discount Updated!');
    }
}
