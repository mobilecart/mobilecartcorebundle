<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CategoryDelete
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryDelete
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
    public function onCategoryDelete(CoreEvent $event)
    {
        // Delete behavior : delete row and cascade delete rows in category_product

        /** @var \MobileCart\CoreBundle\Entity\Category $entity */
        $entity = $event->getEntity();

        try {
            $this->getEntityService()->remove($entity, EntityConstants::CATEGORY);
            $event->setSuccess(true);
            $event->addSuccessMessage('Category Deleted !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while deleting Category');
        }

        $event->flashMessages();
    }
}
