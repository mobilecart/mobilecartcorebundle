<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ContentUpdate
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

    protected function getReturnData()
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

    public function onContentUpdate(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        $this->getEntityService()->persist($entity);

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Content Successfully Updated!'
            );
        }

        if ($formData) {

            // update var values
            $this->getEntityService()
                ->persistVariants($event->getObjectType(), $entity, $formData);
        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {

                foreach($images as $k => $image) {

                    if (!isset($image->sort_order)) {
                        $image->sort_order = 1;
                        $images[$k] = $image;
                    }

                    if (!$image->sort_order) {
                        $image->sort_order = 1;
                        $images[$k] = $image;
                    }
                }

                $this->getEntityService()
                    ->updateImages(EntityConstants::CONTENT_IMAGE, $entity, $images);
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
