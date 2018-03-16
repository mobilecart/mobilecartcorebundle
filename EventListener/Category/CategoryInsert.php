<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CategoryInsert
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryInsert
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
    public function onCategoryInsert(CoreEvent $event)
    {
        $request = $event->getRequest();

        /** @var \MobileCart\CoreBundle\Entity\Category $entity */
        $entity = $event->getEntity();
        $entity->setSlug($this->getEntityService()->slugify($entity->getSlug()));

        $this->getEntityService()->beginTransaction();

        try {
            $this->getEntityService()->persist($entity);
            if ($entity->getItemVarSet() && $event->getFormData()) {
                $this->getEntityService()->persistVariants($entity, $event->getFormData());
            }
            $this->getEntityService()->commit();
            $event->setSuccess(true);
            $event->addSuccessMessage('Category Created !');
        } catch(\Exception $e) {
            $this->getEntityService()->rollBack();
            $this->setSuccess(false);
            $event->addErrorMessage('An error occurred while saving the Category');
            return;
        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {
                try {
                    $this->getEntityService()->updateImages(EntityConstants::CATEGORY_IMAGE, $entity, $images);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving a Category Image');
                }
            }
        }
    }
}
