<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Entity\Product;

class ProductEditReturn
{
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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function onProductEditReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $product = $event->getEntity();

        $config = @ (array) json_decode($product->getConfig());
        $typeSections = [];
        $objectType = EntityConstants::PRODUCT;

        $typeSections['images'] = [
            'section_id'   => 'images',
            'label'        => 'Images',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Widgets/Image:uploader_js.html.twig',
            'images'       => $product->getImages(),
            'image_sizes'  => $this->getImageService()->getImageConfigs($objectType),
            'upload_query' => "?object_type={$objectType}&object_id={$product->getId()}",
        ];

        $categories = [];
        $categoryIds = [];
        $productCats = $product->getCategoryProducts();
        if ($productCats) {
            foreach($productCats as $productCat) {
                $category = $productCat->getCategory();
                $categoryData = $category->getBaseData();
                foreach($categoryData as $k => $v) {
                    if (is_array($v)) {
                        unset($categoryData[$k]);
                    }
                }
                $categories[] = $categoryData;
                $categoryIds[] = $category->getId();
            }
        }

        // todo : retrieve categories with a single query using categoryIds

        $typeSections['categories'] = [
            'section_id'   => 'categories',
            'label'        => 'Categories',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Product/Category:category_tabs.html.twig',
            'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Product/Category:category_tabs_js.html.twig',
            'categories'   => $categories,
            'category_ids' => $categoryIds,
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
                $varSet = $product->getItemVarSet();
                $vars = $varSet
                    ? $varSet->getItemVars()
                    : [];

                $varCodes = [];
                if (isset($config['config_values'])) {
                    foreach($config['config_values'] as $configValue) {
                        $configValue = get_object_vars($configValue);
                        $varCodes[] = isset($configValue['var_code']) ? $configValue['var_code'] : '';
                    }
                }

                foreach($vars as $code => $var) {
                    if (in_array($code, $varCodes)) {
                        $var = $vars[$code];
                        $var->checked = 1;
                        $vars[$code] = $var;
                    }
                }

                $childIds = [];
                $productConfigs = $product->getProductConfigs();
                if ($productConfigs->count()) {
                    foreach($productConfigs as $productConfig) {
                        $childId = $productConfig->getChildProduct()->getId();
                        // enforcing distinct / unique values in array
                        $childIds[$childId] = $childId;
                    }
                    $childIds = array_keys($childIds);
                }

                $typeSections['configproducts'] = [
                    'section_id'   => 'configproducts',
                    'label'        => 'Configured Products',
                    'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Product/Type:product_grid_config_tabs.html.twig',
                    'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Product/Type:product_grid_config_js.html.twig',
                    'vars'         => $vars,
                    'child_ids'    => $childIds,
                    'check_prefix' => 'child-id-', // for shared templates in product-listing.js, todo: remove this
                ];

                $childProducts = $this->getEntityService()->findBy(EntityConstants::PRODUCT, ['id' => $childIds]);
                $returnData['child_products'] = $childProducts;

                break;
            default:

                break;
        }

        $returnData['template_sections'] = $typeSections;

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $product;

        $response = $this->getThemeService()
            ->render('admin', 'Product:edit.html.twig', $returnData);

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
