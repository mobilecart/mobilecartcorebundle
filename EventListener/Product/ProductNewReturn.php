<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Entity\Product;

/**
 * Class ProductNewReturn
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductNewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ImageService
     */
    protected $imageService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
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
     * @param $imageService
     * @return $this
     */
    public function setImageService($imageService)
    {
        $this->imageService = $imageService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ImageService
     */
    public function getImageService()
    {
        return $this->imageService;
    }

    /**
     * @param Event $event
     */
    public function onProductNewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $product = $event->getEntity();
        $request = $event->getRequest();

        $formData = $request->get('form', []);
        $varSetId = isset($formData['var_set_id'])
            ? $formData['var_set_id']
            : 0;

        $varSet = $this->getEntityService()->find(EntityConstants::ITEM_VAR_SET, $varSetId);
        $objectType = EntityConstants::PRODUCT;
        $typeSections = [];

        $typeSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader_js.html.twig',
            'images'       => [],
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}",
        ];

        $typeSections['categories'] = [
            'section_id'   => 'categories',
            'label'        => 'Categories',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Product/Category:category_tabs.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Product/Category:category_tabs_js.html.twig',
            'categories'   => [],
            'category_ids' => [],
        ];

        /*
        $typeSections['relatedproducts'] = array(
            'section_id'  => 'relatedproducts',
            'label'       => 'Related Products',
            'template'    => $this->getThemeService()->getTemplatePath('admin') . 'Product/Type:product_grid_related_tabs.html.twig',
            'js_template' => $this->getThemeService()->getTemplatePath('admin') . 'Product/Type:product_grid_related_js.html.twig',
            'child_ids'    => [],
            'check_prefix' => 'related-id-', // for shared templates in product-listing.js, todo: remove this
        ); //*/

        switch($product->getType()) {
            case Product::TYPE_SIMPLE:

                break;
            case Product::TYPE_CONFIGURABLE:

                $vars = $varSet
                    ? $varSet->getItemVars()
                    : [];

                $childIds = [];

                $typeSections['configproducts'] = [
                    'section_id'   => 'configproducts',
                    'label'        => 'Configured Products',
                    'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Product/Type:product_grid_config_tabs.html.twig',
                    'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Product/Type:product_grid_config_js.html.twig',
                    'vars'         => $vars,
                    'child_ids'    => $childIds,
                    'check_prefix' => 'child-id-', // for shared templates in product-listing.js, todo: remove this
                ];
                $returnData['child_products'] = [];
                break;
            default:

                break;
        }

        $returnData['template_sections'] = $typeSections;

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $product;

        $response = $this->getThemeService()
            ->render('admin', 'Product:new.html.twig', $returnData);

        $event->setResponse($response);
        $event->setReturnData($returnData);
    }
}
