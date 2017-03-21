<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\ProductConfig;
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

            // Doctrine-specific : needed ?
            // $this->getEntityService()->getDoctrine()->getManager()->refresh($entity);

            // get current config
            $productVariantCodes = [];
            $pConfigs = $entity->getProductConfigs();
            if ($pConfigs) {
                foreach($pConfigs as $tmpConfig) {
                    $childProductId = $tmpConfig->getChildProduct()->getId();
                    $varCode = $tmpConfig->getItemVar()->getCode();
                    $productVariantCodes[$childProductId][$varCode] = $tmpConfig;
                }
            }

            // update configurable product information

            $simpleIds = is_array($request->get('simple_ids', []))
                ? array_keys($request->get('simple_ids', []))
                : [];

            $variantCodes = $request->get('config_vars', []);
            $variants = [];

            if ($simpleIds && $variantCodes) {

                // load variants
                $variants = $this->getEntityService()->findBy(EntityConstants::ITEM_VAR, [
                    'code' => $variantCodes
                ]);

                // load products
                $simples = $this->getEntityService()->findBy(EntityConstants::PRODUCT, [
                    'id' => $simpleIds,
                ]);

                if ($simples && $variants) {

                    foreach($simples as $simple) {
                        foreach($variants as $itemVar) {

                            $childProductId = $simple->getId();
                            $varCode = $itemVar->getCode();

                            if (isset($productVariantCodes[$childProductId][$varCode])) {
                                // already have it
                                //  unset it, and whatever is left will be deleted
                                unset($productVariantCodes[$childProductId][$varCode]);
                            } else {
                                // dont already have it
                                //  create it, and dont add to $productVariantCodes
                                $pConfig = new ProductConfig();
                                $pConfig->setProduct($entity)
                                    ->setChildProduct($simple)
                                    ->setItemVar($itemVar);

                                $this->getEntityService()->persist($pConfig);
                            }
                        }
                    }

                    if ($productVariantCodes) {
                        foreach($productVariantCodes as $childProductId => $varCodes) {
                            if ($varCodes) {
                                foreach($varCodes as $varCode) {
                                    if (isset($productVariantCodes[$childProductId][$varCode])) {
                                        $pConfig = $productVariantCodes[$childProductId][$varCode];
                                        $this->getEntityService()->remove($pConfig);
                                    }
                                }
                            }
                        }
                    }

                    $entity->reconfigure();
                    $this->getEntityService()->persist($entity);
                } else {
                    if ($pConfigs) {
                        foreach($pConfigs as $pConfig) {
                            $this->getEntityService()->remove($pConfig);
                        }
                    }
                }
            } else {
                if ($pConfigs) {
                    foreach($pConfigs as $pConfig) {
                        $this->getEntityService()->remove($pConfig);
                    }
                }
            }

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
