<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class DiscountDelete
 * @package MobileCart\CoreBundle\EventListener\Discount
 */
class DiscountDelete
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
    public function onDiscountDelete(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::DISCOUNT);
        $event->addSuccessMessage('Discount Deleted !');
    }
}
