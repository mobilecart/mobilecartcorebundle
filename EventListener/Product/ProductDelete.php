<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ProductDelete
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductDelete
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
    public function onProductDelete(CoreEvent $event)
    {
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

        if ($entity
            && $event->getRequest()->getSession()
            && !$event->getIsMassUpdate()
        ) {
            $event->addSuccessMessage('Product Deleted!');
        }
    }
}
