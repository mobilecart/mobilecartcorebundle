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
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyServiceInterface
     */
    protected $currencyService;

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
     * @param \MobileCart\CoreBundle\Service\CurrencyServiceInterface $currencyService
     * @return $this
     */
    public function setCurrencyService(\MobileCart\CoreBundle\Service\CurrencyServiceInterface $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyServiceInterface
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onProductDuplicate(CoreEvent $event)
    {
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
            $entity->setCurrency($this->getCurrencyService()->getBaseCurrency());
        }

        $entity->setCreatedAt(new \DateTime('now'));

        $this->getEntityService()->beginTransaction();

        try {
            $this->getEntityService()->persist($entity);
        } catch(\Exception $e) {
            $this->getEntityService()->rollBack();
            $event->setSuccess(false);
            $event->addErrorMessage('An error occurred while saving the Product');
            return;
        }

        $id = $entity->getId();
        $newSlug = str_replace('-copy', "-{$id}", $entity->getSlug());
        $newSku = str_replace('-copy', "-{$id}", $entity->getSku());
        $entity->setSlug($newSlug)
            ->setSku($newSku);

        try {
            $this->getEntityService()->persist($entity);
        } catch(\Exception $e) {
            $this->getEntityService()->rollBack();
            $event->setSuccess(false);
            $event->addErrorMessage('An error occurred while saving the Product');
            return;
        }

        foreach($baseData as $k => $v) {
            if (array_key_exists($k, $formData)) {
                unset($formData[$k]);
            }
        }

        if ($formData) {
            try {
                $this->getEntityService()->persistVariants($entity, $formData);
            } catch(\Exception $e) {
                $this->getEntityService()->rollBack();
                $event->setSuccess(false);
                $event->addErrorMessage('An error occurred while saving the Product variants');
                return;
            }
        }

        // update categories
        if ($categories) {
            foreach($categories as $category) {
                $categoryProduct = $this->getEntityService()->getInstance(EntityConstants::CATEGORY_PRODUCT);
                $categoryProduct->setCategory($category);
                $categoryProduct->setProduct($entity);
                try {
                    $this->getEntityService()->persist($categoryProduct);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving a Product Category association');
                    // this isn't a 'critical error'
                }
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

                try {
                    $this->getEntityService()->persist($newImage);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving a Product image');
                    // this isn't a 'critical error'
                }
            }
        }

        $event->setEntity($entity);
        $this->getEntityService()->commit();
        $event->setSuccess(true);
        $event->addSuccessMessage('Product Duplicated !');
    }
}
