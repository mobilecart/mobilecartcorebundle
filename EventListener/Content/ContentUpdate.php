<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ContentUpdate
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentUpdate
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
    public function onContentUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $this->getEntityService()->persist($entity);
        if ($event->getFormData()) {

            $this->getEntityService()
                ->persistVariants($entity, $event->getFormData());
        }

        $event->addSuccessMessage('Content Updated!');

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
    }
}
