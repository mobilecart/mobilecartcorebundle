<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Twig\Extension;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Entity\Product;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class FrontendExtension
 * @package MobileCart\CoreBundle\Twig\Extension
 */
class FrontendExtension extends \Twig_Extension
{
    /**
     * @var bool
     */
    protected $isProduction = false;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \MobileCart\CoreBundle\Service\ImageService
     */
    protected $imageService;

    /**
     * @var
     */
    protected $shippingMethods = [];

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfig;

    /**
     * @var \MobileCart\CoreBundle\Service\MenuService
     */
    protected $menuService;

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            'isProduction' => new \Twig_SimpleFunction('getIsProduction', [$this, 'getIsProduction'], ['is_safe' => ['html']]),
            'imagePath' => new \Twig_SimpleFunction('imagePath', [$this, 'imagePath'], ['is_safe' => ['html']]),
            //'sortable' => new \Twig_SimpleFunction('sortable', [$this, 'sortable'], ['is_safe' => ['html']]),
            'configDecode' => new \Twig_SimpleFunction('configDecode', [$this, 'configDecode'], ['is_safe' => ['html']]),
            'theme' => new \Twig_SimpleFunction('theme', [$this, 'theme'], array('is_safe' => array('html'))),
            'layout' => new \Twig_SimpleFunction('layout', [$this, 'layout'], array('is_safe' => array('html'))),
            'frontend' => new \Twig_SimpleFunction('frontend', [$this, 'frontend'], array('is_safe' => array('html'))),
            'admin' => new \Twig_SimpleFunction('admin', [$this, 'admin'], array('is_safe' => array('html'))),
            'isSpaEnabled' => new \Twig_SimpleFunction('isSpaEnabled', [$this, 'isSpaEnabled'], array('is_safe' => array('html'))),
            'templatePath' => new \Twig_SimpleFunction('templatePath', [$this, 'templatePath'], array('is_safe' => array('html'))),
            'frontendAssetDir' => new \Twig_SimpleFunction('frontendAssetDir', [$this, 'frontendAssetDir'], array('is_safe' => array('html'))),
            'adminAssetDir' => new \Twig_SimpleFunction('adminAssetDir', [$this, 'adminAssetDir'], array('is_safe' => array('html'))),
            'frontendAsset' => new \Twig_SimpleFunction('frontendAsset', [$this, 'frontendAsset'], array('is_safe' => array('html'))),
            'adminAsset' => new \Twig_SimpleFunction('adminAsset', [$this, 'adminAsset'], array('is_safe' => array('html'))),
            'currencySymbol' => new \Twig_SimpleFunction('currencySymbol', [$this, 'currencySymbol'], array('is_safe' => array('html'))),
            'convert' => new \Twig_SimpleFunction('convert', [$this, 'convert'], array('is_safe' => array('html'))),
            'decorate' => new \Twig_SimpleFunction('decorate', [$this, 'decorate'], array('is_safe' => array('html'))),
            'renderPager' => new \Twig_SimpleFunction('renderPager', [$this, 'renderPager'], array('is_safe' => array('html'))),
            'getPages' => new \Twig_SimpleFunction('getPages', [$this, 'getPages'], array('is_safe' => array('html'))),
            'getRedirect' => new \Twig_SimpleFunction('getRedirect', [$this, 'getRedirect'], array('is_safe' => array('html'))),
            'cart' => new \Twig_SimpleFunction('cart', [$this, 'getCart'], array('is_safe' => array('html'))),
            'cartMenu' => new \Twig_SimpleFunction('cartMenu', [$this, 'cartMenu'], ['is_safe' => ['html']]),
            'adminEditLink' => new \Twig_SimpleFunction('adminEditLink', [$this, 'adminEditLink'], array('is_safe' => array('html'))),
            'adminDeleteLink' => new \Twig_SimpleFunction('adminDeleteLink', [$this, 'adminDeleteLink'], array('is_safe' => array('html'))),
            'renderGridField' => new \Twig_SimpleFunction('renderGridField', [$this, 'renderGridField'], array('is_safe' => array('html'))),
            'renderGridBackUrl' => new \Twig_SimpleFunction('renderGridBackUrl', [$this, 'renderGridBackUrl'], array('is_safe' => array('html'))),
            //'categoryList' => new \Twig_SimpleFunction('getCategories', [$this, 'getCategories'], array('is_safe' => array('html'))),
            'cartJson' => new \Twig_SimpleFunction('cartJson', [$this, 'getCartJson'], array('is_safe' => array('html'))),
            'isDiscountEnabled' => new \Twig_SimpleFunction('isDiscountEnabled', [$this, 'getIsDiscountEnabled'], array('is_safe' => array('html'))),
            'isShippingEnabled' => new \Twig_SimpleFunction('isShippingEnabled', [$this, 'getIsShippingEnabled'], array('is_safe' => array('html'))),
            'isMultiShippingEnabled' => new \Twig_SimpleFunction('isMultiShippingEnabled', [$this, 'getIsMultiShippingEnabled'], array('is_safe' => array('html'))),
            'cartHasShipmentMethodId' => new \Twig_SimpleFunction('cartHasShipmentMethodId', [$this, 'cartHasShipmentMethodId'], array('is_safe' => array('html'))),
            'cartHasShipmentMethodCode' => new \Twig_SimpleFunction('cartHasShipmentMethodCode', [$this, 'cartHasShipmentMethodCode'], array('is_safe' => array('html'))),
            'cartTotals' => new \Twig_SimpleFunction('cartTotals', [$this, 'getCartTotals'], array('is_safe' => array('html'))),
            'cartTotal' => new \Twig_SimpleFunction('cartTotal', [$this, 'getCartTotal'], array('is_safe' => array('html'))),
            'itemTotal' => new \Twig_SimpleFunction('itemTotal', [$this, 'itemTotal'], array('is_safe' => array('html'))),
            'shipmentTotal' => new \Twig_SimpleFunction('shipmentTotal', [$this, 'shipmentTotal'], array('is_safe' => array('html'))),
            'discountTotal' => new \Twig_SimpleFunction('discountTotal', [$this, 'discountTotal'], array('is_safe' => array('html'))),
            'taxTotal' => new \Twig_SimpleFunction('taxTotal', [$this, 'taxTotal'], array('is_safe' => array('html'))),
            'grandTotal' => new \Twig_SimpleFunction('grandTotal', [$this, 'grandTotal'], array('is_safe' => array('html'))),
            'cartDiscounts' => new \Twig_SimpleFunction('cartDiscounts', [$this, 'getCartDiscounts'], array('is_safe' => array('html'))),
            'cartItems' => new \Twig_SimpleFunction('cartItems', [$this, 'getCartItems'], array('is_safe' => array('html'))),
            'cartShippingMethods' => new \Twig_SimpleFunction('cartShippingMethods', [$this, 'getCartShippingMethods'], array('is_safe' => array('html'))),
            'cartAllShippingMethods' => new \Twig_SimpleFunction('cartAllShippingMethods', [$this, 'getAllCartShippingMethods'], array('is_safe' => array('html'))),
            'cartShipments' => new \Twig_SimpleFunction('cartShipments', [$this, 'cartShipments'], array('is_safe' => array('html'))),
            'cartShipment' => new \Twig_SimpleFunction('cartShipment', [$this, 'cartShipment'], array('is_safe' => array('html'))),
            'categoryTree' => new \Twig_SimpleFunction('categoryTree', [$this, 'categoryTree'], array('is_safe' => array('html'))),
            'subcategoryList' => new \Twig_SimpleFunction('subcategoryList', [$this, 'subcategoryList'], array('is_safe' => array('html'))),
            'customerName' => new \Twig_SimpleFunction('customerName', [$this, 'getCustomerName'], array('is_safe' => array('html'))),
            'customerHasGroup' => new \Twig_SimpleFunction('customerHasGroup', [$this, 'customerHasGroup'], array('is_safe' => array('html'))),
            'customer' => new \Twig_SimpleFunction('customer', [$this, 'getCustomer'], array('is_safe' => array('html'))),
            'addressLabel' => new \Twig_SimpleFunction('addressLabel', [$this, 'getAddressLabel'], array('is_safe' => array('html'))),
            'customerAddress' => new \Twig_SimpleFunction('customerAddress', [$this, 'customerAddress'], array('is_safe' => array('html'))),
            'shippingStreet' => new \Twig_SimpleFunction('shippingStreet', [$this, 'getShippingStreet'], array('is_safe' => array('html'))),
            'shippingStreet2' => new \Twig_SimpleFunction('shippingStreet2', [$this, 'getShippingStreet2'], array('is_safe' => array('html'))),
            'shippingName' => new \Twig_SimpleFunction('shippingName', [$this, 'getShippingName'], array('is_safe' => array('html'))),
            'isShippingSame' => new \Twig_SimpleFunction('isShippingSame', [$this, 'getIsShippingSame'], array('is_safe' => array('html'))),
            'shippingCity' => new \Twig_SimpleFunction('shippingCity', [$this, 'getShippingCity'], array('is_safe' => array('html'))),
            'shippingRegion' => new \Twig_SimpleFunction('shippingRegion', [$this, 'getShippingRegion'], array('is_safe' => array('html'))),
            'shippingPostcode' => new \Twig_SimpleFunction('shippingPostcode', [$this, 'getShippingPostcode'], array('is_safe' => array('html'))),
            'shippingCountryId' => new \Twig_SimpleFunction('shippingCountryId', [$this, 'getShippingCountryId'], array('is_safe' => array('html'))),
            'customerAddresses' => new \Twig_SimpleFunction('customerAddresses', [$this, 'getCustomerAddresses'], array('is_safe' => array('html'))),
            'billingName' => new \Twig_SimpleFunction('billingName', [$this, 'getBillingName'], array('is_safe' => array('html'))),
            'billingStreet' => new \Twig_SimpleFunction('billingStreet', [$this, 'getBillingStreet'], array('is_safe' => array('html'))),
            'billingStreet2' => new \Twig_SimpleFunction('billingStreet', [$this, 'getBillingStreet2'], array('is_safe' => array('html'))),
            'billingCity' => new \Twig_SimpleFunction('billingCity', [$this, 'getBillingCity'], array('is_safe' => array('html'))),
            'billingRegion' => new \Twig_SimpleFunction('billingRegion', [$this, 'getBillingRegion'], array('is_safe' => array('html'))),
            'billingPostcode' => new \Twig_SimpleFunction('billingPostcode', [$this, 'getBillingPostcode'], array('is_safe' => array('html'))),
            'billingCountryId' => new \Twig_SimpleFunction('billingCountryId', [$this, 'getBillingCountryId'], array('is_safe' => array('html'))),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'money' => new \Twig_SimpleFilter('money', [$this, 'money'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'mobilecart.corebundle.frontend';
    }

    /**
     * @param $isProduction
     * @return $this
     */
    public function setIsProduction($isProduction)
    {
        $this->isProduction = $isProduction;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsProduction()
    {
        return $this->isProduction;
    }

    /**
     *
     * @param $objectType
     * @param $objectData
     * @param $code
     * @param $isDefault
     * @return string
     */
    public function imagePath($objectType, $objectData, $code, $isDefault = 0)
    {
        $fallback = '';
        if (is_array($objectData)) {
            if (isset($objectData['images']) && is_array($objectData['images'])) {
                foreach($objectData['images'] as $imageData) {
                    if (isset($imageData['code']) && $imageData['code'] == $code) {
                        if ($isDefault && isset($imageData['is_default']) && $imageData['is_default']) {
                            return $imageData['path'];
                        } else {
                            $fallback = $imageData['path'];
                        }
                    }
                }
            }
        } elseif (is_object($objectData) && method_exists($objectData, 'getImagePath')) {
            // todo : implement an interface here
            $path = $objectData->getImagePath($code, $isDefault);
            if ($path) {
                return $path;
            }
        } elseif ($objectData instanceof ArrayWrapper && $objectData->getImages()) {
            foreach($objectData->getImages() as $imageData) {
                if (is_object($imageData)) {
                    $imageData = get_object_vars($imageData);
                }
                if (isset($imageData['code']) && $imageData['code'] == $code) {
                    if ($isDefault && isset($imageData['is_default']) && $imageData['is_default']) {
                        return $imageData['path'];
                    } else {
                        $fallback = $imageData['path'];
                    }
                }
            }
        }

        if ($fallback) {
            return $fallback;
        }

        // returns the "placeholder", not the default image
        // todo : rename method to getPlaceholderImage()
        return $this->getImageService()->getDefaultImage($objectType, $code);
    }

    /**
     * @param $val
     * @return string
     */
    public function money($val)
    {
        return number_format($val, 2, '.', '');
    }

    /**
     * @param $code
     * @return string
     */
    public function layout($code)
    {
        return $this->getThemeConfig()->getThemeLayout($code);
    }

    /**
     * @return string
     */
    public function frontend()
    {
        // todo : in the future, look at hostname
        $layout = 'frontend';
        return $this->layout($layout);
    }

    /**
     * @return string
     */
    public function admin()
    {
        // todo : in the future, look at hostname
        $layout = 'admin';
        return $this->layout($layout);
    }

    /**
     * @return string
     */
    public function email()
    {
        // todo : in the future, look at hostname
        $layout = 'email';
        return $this->layout($layout);
    }

    /**
     * @return mixed
     */
    public function isSpaEnabled()
    {
        $code = 'frontend';
        return $this->getThemeConfig()->getIsSpaEnabled($code);
    }

    /**
     * @param $tpl
     * @param $theme
     * @return string
     */
    public function templatePath($tpl, $theme = 'frontend')
    {
        return $this->getThemeConfig()->getTemplatePath($theme) . $tpl;
    }

    /**
     * @param $json
     * @return array
     */
    public function configDecode($json)
    {
        return @ (array) json_decode($json);
    }

    /**
     * @return mixed
     */
    public function frontendAssetDir()
    {
        $dir = $this->getThemeConfig()->getFrontendAssetDir();
        if (substr($dir, 0, 1) != '/') {
            return '/' . $dir;
        }
        return $dir;
    }

    /**
     * @return mixed
     */
    public function adminAssetDir()
    {
        return $this->getThemeConfig()->getAdminAssetDir();
    }

    /**
     * @param $relPath
     * @return string
     */
    public function frontendAsset($relPath)
    {
        return $this->frontendAssetDir() . '/' . $relPath;
    }

    /**
     * @param $relPath
     * @return string
     */
    public function adminAsset($relPath)
    {
        return $this->adminAssetDir() . '/' . $relPath;
    }

    /**
     * @return mixed
     */
    public function currencySymbol()
    {
        $currency = $this->getCartService()->getCurrency();
        if (!$currency) {
            return $this->getCurrencyService()->getBaseSymbol();
        }
        return $this->getCurrencyService()->getSymbol($currency);
    }

    /**
     * @param $themeConfig
     * @return $this
     */
    public function setThemeConfig($themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * @param $menuService
     * @return $this
     */
    public function setMenuService($menuService)
    {
        $this->menuService = $menuService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\MenuService
     */
    public function getMenuService()
    {
        return $this->menuService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCurrencyService();
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
     * @return mixed
     */
    public function getImageService()
    {
        return $this->imageService;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->getCartService()->getSession();
    }

    /**
     * @return string
     */
    public function getRedirect()
    {
        if ($url = $this->getSession()->get('redirect_url')) {
            return $url;
        }
        return '';
    }

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param $router
     * @return $this
     */
    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param $alias
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function cartMenu($alias, array $options = [])
    {
        return $this->menuService->createMenu($alias, $options);
    }

    /**
     * @param $value
     * @param string $to
     * @return mixed
     */
    public function convert($value, $to = '')
    {
        if (!$to) {
            $to = $this->getCartService()->getCurrency();
        }
        return $this->getCurrencyService()->convert($value, $to);
    }

    /**
     * @param $value
     * @param string $to
     * @return mixed
     */
    public function decorate($value, $to = '')
    {
        if (!$to) {
            $to = $this->getCartService()->getCurrency();
        }
        return $this->getCurrencyService()->decorate($value, $to);
    }

    /**
     * @param int $categoryId
     * @param string $class
     * @param int $count
     * @param bool $hideFirst
     * @return string
     */
    public function categoryTree($categoryId, $class='', $count=0, $hideFirst = false)
    {
        $category = is_object($categoryId)
            ? $categoryId
            : $this->getEntityService()->find(EntityConstants::CATEGORY, $categoryId);

        if (!$category) {
            return '';
        }

        $out = '<ul>';
        if ($class && !$count) {
            $out = "<ul class=\"{$class}\">";
        }

        if ($children = $category->getChildCategories()) {

            if ((!$count && !$hideFirst) || $count) {

                $out .= '<li>';
                if (!$count && !$hideFirst) {
                    $out .= $category->getName();
                }

                $out .= '<i class="fa fa-angle-down"> </i><ul>';
            }

            foreach($children as $child) {
                $childCategories = $child->getChildCategories();
                if (count($childCategories)) {
                    $out .= '<li>' .
                    $out .= $child->getName();
                    $out .= '<i class="fa fa-angle-down"> </i>';
                    $out .= $this->categoryTree($child, '', $count+1);
                    $out .= '</li>';
                } else {
                    $link = $this->getRouter()->generate('cart_category_products', ['slug' => $child->getSlug()]);
                    $out .= '<li><a href="'. $link .'">' . $child->getName() . '</a></li>';
                }
            }

            if ((!$count && !$hideFirst) || $count) {
                $out .= '</ul></li>';
            }
        } else {
            $link = $this->getRouter()->generate('cart_category_products', ['slug' => $category->getSlug()]);
            $out .= '<li><a href="'. $link .'">' . $category->getName() . '</a></li>';
        }
        $out .= '</ul>';
        return $out;
    }

    /**
     * @param $categoryId
     * @return string
     */
    public function subcategoryList($categoryId)
    {
        $category = is_object($categoryId)
            ? $categoryId
            : $this->getEntityService()->find(EntityConstants::CATEGORY, $categoryId);

        if (!$category) {
            return '';
        }

        $out = '<ul>';
        if ($children = $category->getChildCategories()) {
            foreach($children as $child) {
                $link = $this->getRouter()->generate('cart_category_products', ['slug' => $child->getSlug()]);
                $out .= '<li><a href="' . $link . '">' . $child->getName() . '</a></li>';
            }
        }
        $out .= '</ul>';

        return $out;
    }

    /**
     * @param $objectType
     * @param $id
     * @return string
     */
    public function adminEditLink($objectType, $id)
    {
        $routeId = $this->getThemeConfig()->getAdminEditRoute($objectType);
        return $this->router->generate($routeId, ['id' => $id]);

        /*
        if (is_int(strpos($objectType, 'item_var_option_'))
            && isset($_GET['datatype'])
            && in_array($_GET['datatype'], ['datetime','decimal','int','text','varchar'])) {

            $url = $this->router->generate($routeId, ['id' => $id, 'datatype' => $_GET['datatype']]);
        } //*/
    }

    /**
     * @param $objectType
     * @param $id
     * @return string
     */
    public function adminDeleteLink($objectType, $id)
    {
        $routeId = $this->getThemeConfig()->getAdminDeleteRoute($objectType);
        return $this->router->generate($routeId, ['id' => $id]);
    }

    /**
     * @param $objectType
     * @param $field
     * @param $value
     * @return mixed
     */
    public function renderGridField($objectType, $field, $value)
    {
        /*
        if ($field == 'id' && $this->getThemeConfig()->getAdminEditRoute($objectType)) {
            $routeId = $this->getThemeConfig()->getAdminEditRoute($objectType);
            $url = $this->router->generate($routeId, ['id' => $value]);
            if (is_int(strpos($objectType, 'item_var_option_'))
                && isset($_GET['datatype'])
                && in_array($_GET['datatype'], ['datetime','decimal','int','text','varchar'])) {

                $url = $this->router->generate($routeId, ['id' => $value, 'datatype' => $_GET['datatype']]);
            }
            return '<a href="' . $url . '">' . $value . '</a>';
        } //*/

        switch($objectType) {
            case EntityConstants::ITEM_VAR:
                switch($field) {
                    case 'action':
                        if (strlen($value)) {
                            return '<a href="' . $value . '">Options</a>';
                        }
                        return '';
                        break;
                    default:
                        return $value;
                        break;
                }
                break;
            case EntityConstants::PRODUCT:
                switch($field) {
                    case 'type':
                        $types = Product::getTypes();
                        // todo:
                        //$types = $this->getProductConfigService()->getProductTypes();
                        return isset($types[$value]) ? $types[$value] : $value;
                        break;
                    case 'price':
                        return $this->decorate($value);
                        break;
                    case 'is_in_stock':
                        return $value ? 'Yes' : 'No';
                        break;
                    default:
                        return $value;
                        break;
                }
                break;
            case EntityConstants::ORDER:
                switch($field) {
                    case 'reference_nbr':
                        $orderId = (int) substr($value, 1);
                        $url = $this->router->generate('cart_admin_order_edit', ['id' => $orderId]);
                        return '<a href="' . $url . '">' . $value . '</a>';
                        break;
                    case 'total':
                        return number_format($value, 2, '.', '');
                        break;
                    default:
                        return $value;
                        break;
                }
                break;
            case EntityConstants::ORDER_PAYMENT:
                switch($field) {
                    case 'base_amount':
                        return number_format($value, 2, '.', '');
                        break;
                    default:
                        return $value;
                        break;
                }
                break;
            case EntityConstants::ORDER_ITEM:
                switch($field) {
                    case 'reference_nbr':
                        $orderId = (int) substr($value, 1);
                        $url = $this->router->generate('cart_admin_order_edit', ['id' => $orderId]);
                        return '<a href="' . $url . '">' . $value . '</a>';
                        break;
                    case 'price':
                        return number_format($value, 2, '.', '');
                        break;
                    default:
                        return $value;
                        break;
                }
                break;
            default:
                return $value;
                break;
        }
    }

    /**
     * @param $route
     * @return string
     */
    public function renderGridBackUrl($route)
    {
        $url = $this->getRouter()->generate($route);
        if ($queryString = $this->getSession()->get($route)) {
            $url .= '?' . $queryString;
        }
        return $url;
    }

    /**
     * @param $currentPage
     * @param $lastPageNbr
     * @return array
     */
    public function getPages($currentPage, $lastPageNbr)
    {
        $pages = [];
        for ($x=1; $x <= $lastPageNbr; $x++) {
            if ($x == $currentPage) {
                $pages[] = $x;
            } else if ($x == 1 || $x == $lastPageNbr) {
                $pages[] = $x;
            } else if ($x == ($currentPage - 1) ||
                ($x > $currentPage && $x < ($currentPage + 3)) ||
                ($x > $currentPage && $x <= ($currentPage + 10) && $x % 10 == 0) ) {

                $pages[] = $x;
            } else if ($lastPageNbr <= 100 && $x % 20 == 0) {
                $pages[] = $x;
            } else if ($lastPageNbr >= 100 && $lastPageNbr <= 200 && $x % 25 == 0) {
                $pages[] = $x;
            } else if ($lastPageNbr <= 2000 && $x % 200 == 0) {
                $pages[] = $x;
            }
        }
        return $pages;
    }

    /**
     * @param array $pages
     * @param int $currentPage
     * @return string
     */
    public function renderPagerSmart(array $pages, $currentPage)
    {
        $html = '<ul class="pagination">';

        foreach ($pages as $page) {
            if ($page == $currentPage) {
                $html .= '<li class="active"><a href="javascript:;">'.$page.'</a></li>';
            } else {
                $html .= '<li><a href="javascript:;">'.$page.'</a></li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    /**
     * @param array $pages
     * @param int $currentPage
     * @return string
     */
    public function renderPager(array $pages, $currentPage)
    {
        $prevDisabled = ($currentPage == 1)
            ? 'disabled'
            : '';

        $nextDisabled = (count($pages) == $currentPage)
            ? 'disabled'
            : '';

        $html = '<button type="button" class="btn btn-default ' . $prevDisabled . '" aria-label="Previous">'.
                '<span aria-hidden="true">&laquo;</span></button>' .
                '<input type="number" class="number" value="' . $currentPage . '" />'.
                '<button type="button" class="btn btn-default ' . $nextDisabled . '" aria-label="Next">'.
                '<span aria-hidden="true">&raquo;</span></button>';

        return $html;
    }

    /**
     * @return \MobileCart\CoreBundle\CartComponent\Cart
     */
    public function getCart()
    {
        return $this->getCartService()->getCart();
    }

    /**
     * @return string
     */
    public function getCartJson()
    {
        return $this->getCartService()->getCart()->toJson();
    }

    /**
     * @param $id
     * @param $addressId
     * @param $srcAddressKey
     * @return mixed
     */
    public function cartHasShipmentMethodId($id, $addressId='main', $srcAddressKey='main')
    {
        return $this->getCartService()->getCart()->hasShipmentMethodId($id, $addressId, $srcAddressKey);
    }

    /**
     * @param $code
     * @param $addressId
     * @param $srcAddressKey
     * @return mixed
     */
    public function cartHasShipmentMethodCode($code, $addressId='main', $srcAddressKey='main')
    {
        return $this->getCartService()->getCart()->hasShipmentMethodCode($code, $addressId, $srcAddressKey);
    }

    /**
     * @return array
     */
    public function cartShipments()
    {
        return $this->getCartService()->getCart()->getShipments();
    }

    /**
     * @return object|bool
     */
    public function cartShipment()
    {
        $shipments = $this->cartShipments();
        if (is_array($shipments) && isset($shipments[0])) {
            return $shipments[0];
        }

        return false;
    }

    /**
     * @return \MobileCart\CoreBundle\CartComponent\Total[]|array
     */
    public function getCartTotals()
    {
        return $this->getCartService()->getTotals();
    }

    /**
     * @param $key
     * @return \MobileCart\CoreBundle\CartComponent\Total|null
     */
    public function getCartTotal($key)
    {
        $totals = $this->getCartTotals();
        if ($totals) {
            foreach($totals as $total) {
                if ($total->getKey() == $key) {
                    return $total;
                }
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function itemTotal()
    {
        return $this->decorate($this->getCartTotal('items')->getValue());
    }

    /**
     * @return mixed
     */
    public function shipmentTotal()
    {
        return $this->decorate($this->getCartTotal('shipments')->getValue());
    }

    /**
     * @return mixed
     */
    public function discountTotal()
    {
        return $this->decorate($this->getCartTotal('discounts')->getValue());
    }

    /**
     * @return mixed
     */
    public function taxTotal()
    {
        return $this->decorate($this->getCartTotal('tax')->getValue());
    }

    /**
     * @return mixed
     */
    public function grandTotal()
    {
        return $this->decorate($this->getCartTotal('grand_total')->getValue());
    }

    /**
     * @return bool
     */
    public function getIsDiscountEnabled()
    {
        return $this->getCartService()->getDiscountService()->getIsDiscountEnabled();
    }

    /**
     * @return bool
     */
    public function getIsShippingEnabled()
    {
        return $this->getCartService()->getShippingService()->getIsShippingEnabled();
    }

    /**
     * @return bool
     */
    public function getIsMultiShippingEnabled()
    {
        return $this->getCartService()->getShippingService()->getIsMultiShippingEnabled();
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return mixed
     */
    public function getCartShippingMethods($addressId='main', $srcAddressKey='main')
    {
        return $this->getCartService()->getShippingMethods($addressId, $srcAddressKey);
    }

    /**
     * @return array
     */
    public function getAllCartShippingMethods()
    {
        return $this->getCartService()->getAllShippingMethods();
    }

    /**
     * @return \MobileCart\CoreBundle\CartComponent\Item[]
     */
    public function getCartItems()
    {
        return $this->getCartService()->getCart()->getItems();
    }

    /**
     * @return \MobileCart\CoreBundle\CartComponent\Customer
     */
    public function getCustomer()
    {
        return $this->getCartService()->getCart()->getCustomer();
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        $customer = $this->getCustomer();

        if ($customer->getFirstName()) {
            return $customer->getFirstName();
        } else if ($customer->getEmail()) {
            return $customer->getEmail();
        }

        return 'Guest';
    }

    /**
     * @param $group
     * @return bool
     */
    public function customerHasGroup($group)
    {
        return $this->getCartService()->customerHasGroup($group);
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getCustomerAddress($addressId='main')
    {
        return $this->getCartService()->getCustomerAddress($addressId);
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getShippingName($addressId='main')
    {
        return $this->getCustomerAddress($addressId)->getName();
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getShippingStreet($addressId='main')
    {
        return $this->getCustomerAddress($addressId)->getStreet();
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getShippingStreet2($addressId='main')
    {
        return $this->getCustomerAddress($addressId)->getStreet2();
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getShippingCity($addressId='main')
    {
        return $this->getCustomerAddress($addressId)->getCity();
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getShippingRegion($addressId='main')
    {
        return $this->getCustomerAddress($addressId)->getRegion();
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getShippingPostcode($addressId='main')
    {
        return $this->getCustomerAddress($addressId)->getPostcode();
    }

    /**
     * @param string $addressId
     * @return mixed
     */
    public function getShippingCountryId($addressId='main')
    {
        return $this->getCustomerAddress($addressId)->getCountryId();
    }

    /**
     * @param $addressId
     * @return string
     */
    public function getAddressLabel($addressId)
    {
        return $this->getCartService()->addressLabel($addressId);
    }

    /**
     * @return array|\MobileCart\CoreBundle\CartComponent\CustomerAddress[]
     */
    public function getCustomerAddresses()
    {
        return $this->getCartService()->getCustomerAddresses();
    }

    /**
     * @return bool
     */
    public function getIsShippingSame()
    {
        return (bool) $this->getCustomer()->getIsShippingSame();
    }

    /**
     * @return mixed
     */
    public function getBillingName()
    {
        return $this->getCustomer()->getBillingName();
    }

    /**
     * @return mixed
     */
    public function getBillingStreet()
    {
        return $this->getCustomer()->getBillingStreet();
    }

    /**
     * @return mixed
     */
    public function getBillingStreet2()
    {
        return $this->getCustomer()->getBillingStreet2();
    }

    /**
     * @return mixed
     */
    public function getBillingCity()
    {
        return $this->getCustomer()->getBillingCity();
    }

    /**
     * @return mixed
     */
    public function getBillingRegion()
    {
        return $this->getCustomer()->getBillingRegion();
    }

    /**
     * @return mixed
     */
    public function getBillingPostcode()
    {
        return $this->getCustomer()->getBillingPostcode();
    }

    /**
     * @return mixed
     */
    public function getBillingCountryId()
    {
        return $this->getCustomer()->getBillingCountryId();
    }
}
