<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Entity\Product;

/**
 * Class ProductNewReturn
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductNewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
     * @param CoreEvent $event
     */
    public function onProductNewReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $varSet = $entity->getItemVarSet();
        $objectType = EntityConstants::PRODUCT;
        $typeSections = [];

        $typeSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getAdminTemplatePath() . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getAdminTemplatePath() . 'Widgets/Image:uploader_js.html.twig',
            'images'       => [],
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}",
        ];

        $typeSections['categories'] = [
            'section_id'   => 'categories',
            'label'        => 'Categories',
            'template'     => $this->getThemeService()->getAdminTemplatePath() . 'Product/Category:category_tabs.html.twig',
            'js_template'  => $this->getThemeService()->getAdminTemplatePath() . 'Product/Category:category_tabs_js.html.twig',
            'categories'   => [],
            'category_ids' => [],
        ];

        /*
        $typeSections['relatedproducts'] = array(
            'section_id'  => 'relatedproducts',
            'label'       => 'Related Products',
            'template'    => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_related_tabs.html.twig',
            'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_related_js.html.twig',
            'child_ids'    => [],
            'check_prefix' => 'related-id-', // for shared templates in product-listing.js, todo: remove this
        ); //*/

        switch($entity->getType()) {
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
                    'template'     => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_config_tabs.html.twig',
                    'js_template'  => $this->getThemeService()->getAdminTemplatePath() . 'Product/Type:product_grid_config_js.html.twig',
                    'vars'         => $vars,
                    'child_ids'    => $childIds,
                    'check_prefix' => 'child-id-', // for shared templates in product-listing.js, todo: remove this
                ];

                $event->setReturnData('child_products', []);

                break;
            default:

                break;
        }

        $event->setReturnData('entity', $entity);
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('template_sections', $typeSections);

        $event->setResponse($this->getThemeService()->renderAdmin(
            'Product:new.html.twig',
            $event->getReturnData()
        ));
    }
}
