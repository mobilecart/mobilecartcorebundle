<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ContentInsert
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentInsert
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
    public function onContentInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        $this->getEntityService()->persist($entity);

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Content Created!'
            );
        }

        if ($formData) {

            $this->getEntityService()
                ->persistVariants($entity, $formData);

        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {
                $this->getEntityService()->updateImages(EntityConstants::CONTENT_IMAGE, $entity, $images);
            }
        }

        // update slots
        if ($slots = $request->get('slots', [])) {
            $sortOrder = 1;
            foreach($slots as $k => $slot) {
                $slots[$k]['sort_order'] = $sortOrder;
                $sortOrder++;
            }

            $this->getEntityService()->updateContentSlots($entity, $slots);
        }

        $event->setReturnData($returnData);
    }
}
