<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\Product;

class ProductInsert
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

    public function onProductInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();

        $entity = $event->getEntity();
        if (!$entity->getCurrency()) {
            // todo : use currency service
            $entity->setCurrency('USD');
        }

        $formData = $event->getFormData();

        $fulltextData = $entity->getBaseData();
        foreach($fulltextData as $k => $v) {
            if (is_numeric($fulltextData[$k]) || is_array($v)) {
                unset($fulltextData[$k]);
            }
        }
        $entity->setFulltextSearch(implode(' ', $fulltextData));

        $this->getEntityService()->persist($entity);
        if ($formData) {

            $this->getEntityService()
                ->persistVariants($entity, $formData);
        }

        if ($entity->getType() == Product::TYPE_CONFIGURABLE) {
            $this->getEntityService()->getDoctrine()->getManager()->refresh($entity);
            $entity->reconfigure();
            $this->getEntityService()->persist($entity);
        }

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Product Created!'
            );
        }

        // update categories
        $postedIds = $request->get('category_ids', []);
        if ($postedIds) {
            $postedIds = array_keys($postedIds); // keys from: r[x] = "on"
            foreach($postedIds as $categoryId) {
                $categoryProduct = $this->getEntityService()->getInstance(EntityConstants::CATEGORY_PRODUCT);
                $category = $this->getEntityService()->find(EntityConstants::CATEGORY, $categoryId);
                if ($category) {
                    $categoryProduct->setCategory($category);
                    $categoryProduct->setProduct($entity);
                    $this->getEntityService()->persist($categoryProduct);
                }
            }
        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {
                $this->getEntityService()->updateImages(EntityConstants::PRODUCT_IMAGE, $entity, $images);
            }
        }

        $event->setReturnData($returnData);
    }
}
