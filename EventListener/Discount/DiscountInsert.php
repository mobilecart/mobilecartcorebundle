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
    public function onDiscountInsert(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Discount $entity */
        $entity = $event->getEntity();
        if ($entity->getAppliedTo() != Discount::APPLIED_TO_SPECIFIED) {
            $entity->setPreConditions('{}');
            $entity->setTargetConditions('{}');
        }

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Discount Created !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving the Discount');
        }
    }
}
