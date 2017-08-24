<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CategoryUpdate
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryUpdate
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
    public function onCategoryUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $request = $event->getRequest();

        $entity->setSlug($this->getEntityService()->slugify($entity->getSlug()));
        $this->getEntityService()->persist($entity);

        if ($event->getFormData()) {

            // update var values
            $this->getEntityService()
                ->persistVariants($entity, $event->getFormData());
        }

        $event->addSuccessMessage('Category Updated!');

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {
                $this->getEntityService()->updateImages(EntityConstants::CATEGORY_IMAGE, $entity, $images);
            }
        }
    }
}
