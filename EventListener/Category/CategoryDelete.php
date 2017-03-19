<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CategoryDelete
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var Event
     */
    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

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
     * @param Event $event
     */
    public function onCategoryDelete(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        // remove category_product
        $categoryProducts = $this->getEntityService()->findBy(EntityConstants::CATEGORY_PRODUCT, [
            'category' => $entity->getId(),
        ]);

        if ($categoryProducts) {
            foreach($categoryProducts as $categoryProduct) {
                $this->getEntityService()->remove($categoryProduct, EntityConstants::CATEGORY_PRODUCT);
            }
        }

        $this->getEntityService()->remove($entity, EntityConstants::CATEGORY);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Category Deleted!'
            );
        }

        $event->setReturnData($returnData);
    }
}
