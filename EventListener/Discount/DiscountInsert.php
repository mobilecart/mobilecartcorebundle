<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Discount;

/**
 * Class DiscountInsert
 * @package MobileCart\CoreBundle\EventListener\Discount
 */
class DiscountInsert
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
    public function onDiscountInsert(CoreEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity->getAppliedTo() != Discount::APPLIED_TO_SPECIFIED) {
            $entity->setPreConditions('{}');
            $entity->setTargetConditions('{}');
        }
        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Discount Created!');
    }
}
