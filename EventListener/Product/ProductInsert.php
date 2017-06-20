<?php

namespace MobileCart\CoreBundle\EventListener\Product;

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

        $entity->setFulltextSearch(implode(' ', $fulltextData))
            ->setCreatedAt(new \DateTime('now'));

        $this->getEntityService()->persist($entity);

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

                            $this->getEntityService()->persist($pConfig);

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
            $this->getEntityService()->persist($entity);
        }

        if ($formData) {

            $this->getEntityService()
                ->persistVariants($entity, $formData);
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

        if ($entity
            && $entity->getId()
            && $request->getSession()
        ) {

            $request->getSession()->getFlashBag()->add(
                'success',
                'Product Created!'
            );
        }

        $event->setReturnData($returnData);
    }
}
