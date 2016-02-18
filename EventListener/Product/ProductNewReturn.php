<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Entity\Product;

class ProductNewReturn
{
    protected $request;

    protected $varSet;

    protected $entityService;

    protected $imageService;

    protected $themeService;

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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
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

    public function setImageService($imageService)
    {
        $this->imageService = $imageService;
        return $this;
    }

    public function getImageService()
    {
        return $this->imageService;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setVarSet($varSet)
    {
        $this->varSet = $varSet;
        return $this;
    }

    public function getVarSet()
    {
        return $this->varSet;
    }

    public function onProductNewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $product = $event->getEntity();

        $varSet = $this->getVarSet();
        $objectType = EntityConstants::PRODUCT;
        $typeSections = [];

        $typeSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader_js.html.twig',
            'images'       => [],
            'image_sizes'  => $this->getImageService()->getImageSizes($objectType),
            'upload_query' => "?object_type={$objectType}",
        ];

        $typeSections['categories'] = [
            'section_id'   => 'category',
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
