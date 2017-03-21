<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Entity\ProductConfig;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\Product;

/**
 * Class ProductInsert
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductInsert
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
    public function onProductInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

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

        // update configurable product information

        $simpleIds = is_array($request->get('simple_ids', []))
            ? array_keys($request->get('simple_ids', []))
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
                foreach ($simples as $simple) {
                    foreach($variants as $itemVar) {

                        $pConfig = new ProductConfig();
                        $pConfig->setProduct($entity)
                            ->setChildProduct($simple)
                            ->setItemVar($itemVar);

                        $this->getEntityService()->persist($pConfig);
                    }
                }

                $entity->reconfigure();
                $this->getEntityService()->persist($entity);
            }
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
