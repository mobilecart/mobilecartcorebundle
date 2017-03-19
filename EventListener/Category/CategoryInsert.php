<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CategoryInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
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
    public function onCategoryInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        $this->getEntityService()->persist($entity);
        if ($formData) {

            $this->getEntityService()
                ->persistVariants($entity, $formData);
        }

        if ($entity && $request->getSession()) {

            $request->getSession()->getFlashBag()->add(
                'success',
                'Category Created!'
            );
        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {
                $this->getEntityService()->updateImages(EntityConstants::CATEGORY_IMAGE, $entity, $images);
            }
        }

        $event->setReturnData($returnData);
    }
}
