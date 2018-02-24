<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\Product;

/**
 * Class ProductInsert
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductInsert
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
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService
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
    public function onProductInsert(CoreEvent $event)
    {
        $request = $event->getRequest();
        /** @var \MobileCart\CoreBundle\Entity\Product $entity */
        $entity = $event->getEntity();
        if (!$entity->getCurrency()) {
            $entity->setCurrency($this->getCurrencyService()->getBaseCurrency());
        }

        $formData = $event->getFormData();
        $fulltextData = $entity->getBaseData();
        foreach($fulltextData as $k => $v) {
            if (is_numeric($fulltextData[$k]) || is_array($v)) {
                unset($fulltextData[$k]);
            }
        }

        $entity->setFulltextSearch(implode(' ', $fulltextData))
            ->setCreatedAt(new \DateTime('now'))
            ->setSlug($this->getEntityService()->slugify($entity->getSlug()));

        $this->getEntityService()->beginTransaction();

        try {
            $this->getEntityService()->persist($entity);
        } catch(\Exception $e) {
            $this->getEntityService()->rollBack();
            $event->setSuccess(false);
            $event->addErrorMessage('An error occurred while saving the Product');
            return;
        }

        // update configurable product information
        if ($entity->getType() == Product::TYPE_CONFIGURABLE) {

            $simpleIds = is_array($request->get('simple_ids', []))
                ? $request->get('simple_ids', [])
                : [];

            $variants = [];
            if ($simpleIds) {
                $variantCodes = $request->get('config_vars', []);
                if ($variantCodes) {
                    $variants = $this->getEntityService()->findBy(EntityConstants::ITEM_VAR, [
                        'code' => $variantCodes
                    ]);
                }

                // load products
                $simples = $this->getEntityService()->findBy(EntityConstants::PRODUCT, [
                    'id' => $simpleIds,
                ]);

                if ($simples && $variants) {
                    foreach($simples as $simple) {
                        foreach($variants as $itemVar) {

                            $pConfig = $this->getEntityService()->getInstance(EntityConstants::PRODUCT_CONFIG);
                            $pConfig->setProduct($entity)
                                ->setChildProduct($simple)
                                ->setItemVar($itemVar);

                            try {
                                $this->getEntityService()->persist($pConfig);
                            } catch(\Exception $e) {
                                $this->getEntityService()->rollBack();
                                $event->setSuccess(false);
                                $event->addErrorMessage('An error occurred while saving the Product configuration');
                                return;
                            }

                            $entity->addProductConfig($pConfig);

                            $simpleValue = $simple->getData($itemVar->getCode());
                            if (isset($formData[$itemVar->getCode()])) {

                                if (!is_array($formData[$itemVar->getCode()])) {
                                    $aValue = $formData[$itemVar->getCode()];
                                    $formData[$itemVar->getCode()] = [$aValue];
                                }

                                if (!in_array($simpleValue, $formData[$itemVar->getCode()])) {
                                    $formData[$itemVar->getCode()][] = $simpleValue;
                                }

                            } else {
                                $formData[$itemVar->getCode()] = [$simpleValue];
                            }
                        }
                    }
                }
            }

            $entity->reconfigure();

            try {
                $this->getEntityService()->persist($entity);
            } catch(\Exception $e) {
                $this->getEntityService()->rollBack();
                $event->setSuccess(false);
                $event->addErrorMessage('An error occurred while saving the Product');
                return;
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
        $postedIds = $request->get('category_ids', []);
        if ($postedIds) {
            foreach($postedIds as $categoryId) {
                $categoryProduct = $this->getEntityService()->getInstance(EntityConstants::CATEGORY_PRODUCT);
                $category = $this->getEntityService()->find(EntityConstants::CATEGORY, $categoryId);
                if ($category) {
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
        }

        // update images
        if ($imageJson = $request->get('images_json', [])) {
            $images = (array) @ json_decode($imageJson);
            if ($images) {
                try {
                    $this->getEntityService()->updateImages(EntityConstants::PRODUCT_IMAGE, $entity, $images);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving a Product image association');
                    // this isn't a 'critical error'
                }
            }
        }

        $this->getEntityService()->commit();
        $event->setSuccess(true);
        $event->addSuccessMessage('Product Created !');
    }
}
