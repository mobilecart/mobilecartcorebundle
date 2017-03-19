<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Entity\Product;

/**
 * Class ProductUpdate
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductUpdate
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
    public function onProductUpdate(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        $fulltextData = $entity->getBaseData();
        foreach($fulltextData as $k => $v) {
            if (is_numeric($fulltextData[$k]) || is_array($v)) {
                unset($fulltextData[$k]);
            }
        }
        $entity->setFulltextSearch(implode(' ', $fulltextData));

        $this->getEntityService()->persist($entity);
        if ($formData) {

            // update var values
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
                'Product Updated!'
            );
        }

        // update categories
        $categoryIds = $entity->getCategoryIds();
        $postedIds = $request->get('category_ids', []);
        $postedIds = array_keys($postedIds); // keys from: r[x] = "on"
        $removed = array_diff($categoryIds, $postedIds);
        $added = array_diff($postedIds, $categoryIds);

        if ($removed) {
            foreach($entity->getCategoryProducts() as $categoryProduct) {
                if (in_array($categoryProduct->getCategory()->getId(), $removed)) {
                    $this->getEntityService()->remove($categoryProduct);
                }
            }
        }

        if ($added) {
            foreach($added as $categoryId) {
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
