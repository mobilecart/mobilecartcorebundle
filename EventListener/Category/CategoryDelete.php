<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CategoryDelete
{

    protected $entityService;

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

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function onCategoryDelete(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

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
