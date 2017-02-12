<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ProductDelete
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

    public function onProductDelete(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();

        // remove tier prices
        $tierPrices = $this->getEntityService()->findBy(EntityConstants::PRODUCT_TIER_PRICE, [
            'product' => $entity->getId(),
        ]);

        if ($tierPrices) {
            foreach($tierPrices as $tierPrice) {
                $this->getEntityService()->remove($tierPrice, EntityConstants::PRODUCT_TIER_PRICE);
            }
        }

        // remove category_product
        $categoryProducts = $this->getEntityService()->findBy(EntityConstants::CATEGORY_PRODUCT, [
            'product' => $entity->getId(),
        ]);

        if ($categoryProducts) {
            foreach($categoryProducts as $categoryProduct) {
                $this->getEntityService()->remove($categoryProduct, EntityConstants::CATEGORY_PRODUCT);
            }
        }

        // remove product images
        $productImages = $this->getEntityService()->findBy(EntityConstants::PRODUCT_IMAGE, [
            'parent' => $entity->getId(),
        ]);

        if ($productImages) {
            foreach($productImages as $productImage) {
                $this->getEntityService()->remove($productImage, EntityConstants::PRODUCT_IMAGE);
            }
        }

        // remove product configs
        $productConfigs = $this->getEntityService()->findBy(EntityConstants::PRODUCT_CONFIG, [
            'product' => $entity->getId(),
        ]);

        if ($productConfigs) {
            foreach($productConfigs as $productConfig) {
                $this->getEntityService()->remove($productConfig, EntityConstants::PRODUCT_CONFIG);
            }
        }

        // remove product configs
        $productConfigs = $this->getEntityService()->findBy(EntityConstants::PRODUCT_CONFIG, [
            'child_product' => $entity->getId(),
        ]);

        if ($productConfigs) {
            foreach($productConfigs as $productConfig) {
                $this->getEntityService()->remove($productConfig, EntityConstants::PRODUCT_CONFIG);
            }
        }

        $this->getEntityService()->remove($entity, EntityConstants::PRODUCT);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'Product Deleted!'
            );
        }

        $event->setReturnData($returnData);
    }
}
