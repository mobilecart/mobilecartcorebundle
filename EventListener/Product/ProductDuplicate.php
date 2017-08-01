<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ProductDuplicate
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductDuplicate
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
    public function onProductDuplicate(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $origEntity = $event->getEntity();
        $formData = $origEntity->getData();
        $baseData = $origEntity->getBaseData();
        unset($formData['id']);

        $categories = $origEntity->getCategories();
        $images = $origEntity->getImages();

        $entity = $this->getEntityService()->getInstance($event->getObjectType());
        $entity->setData($formData);
        $entity->setSlug($origEntity->getSlug() . '-copy');
        $entity->setSku($origEntity->getSku() . '-copy');
        $entity->setItemVarSet($origEntity->getItemVarSet());

        if (!$entity->getCurrency()) {
            // todo : use currency service
            $entity->setCurrency('USD');
        }

        $entity->setCreatedAt(new \DateTime('now'));

        $this->getEntityService()->persist($entity);

        $id = $entity->getId();
        $newSlug = str_replace('-copy', "-{$id}", $entity->getSlug());
        $newSku = str_replace('-copy', "-{$id}", $entity->getSku());
        $entity->setSlug($newSlug)
            ->setSku($newSku);

        $this->getEntityService()->persist($entity);

        foreach($baseData as $k => $v) {
            if (array_key_exists($k, $formData)) {
                unset($formData[$k]);
            }
        }

        if ($formData) {

            $this->getEntityService()
                ->persistVariants($entity, $formData);
        }

        // update categories
        if ($categories) {
            foreach($categories as $category) {
                $categoryProduct = $this->getEntityService()->getInstance(EntityConstants::CATEGORY_PRODUCT);
                $categoryProduct->setCategory($category);
                $categoryProduct->setProduct($entity);
                $this->getEntityService()->persist($categoryProduct);
            }
        }

        // update images
        if ($images) {
            foreach($images as $image) {
                $imgData = $image->getData();
                unset($imgData['id']);
                $newImage = $this->getEntityService()->getInstance(EntityConstants::PRODUCT_IMAGE);
                $newImage->setData($imgData)
                    ->setParent($entity);

                $this->getEntityService()->persist($newImage);
            }
        }

        $event->setEntity($entity);
        $event->setReturnData($returnData);
    }
}
