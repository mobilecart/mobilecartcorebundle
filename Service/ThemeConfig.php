<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

class ThemeConfig
{

    const KEY_LAYOUT = 'layout';
    const KEY_TEMPLATE = 'template';
    const KEY_SPA = 'spa'; // single page app

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $frontendTheme = 'frontend';

    /**
     * @var string
     */
    protected $adminTheme = 'admin';

    /**
     * @var array
     */
    protected $adminEditRoutes = [];

    /**
     * Template options for each object_type eg content, product, category, etc
     *  r[object_type][code] = value
     *
     * @var array
     */
    protected $objectTypeTemplates = [];

    /**
     * @param $code
     * @param $layout string : layout string
     * @param $tplPath string : template path
     * @param $enableSpa int : whether the theme has a single page application
     * @return $this
     */
    public function setTheme($code, $layout, $tplPath, $enableSpa = 0)
    {
        $this->config[$code] = [];
        $this->config[$code][self::KEY_LAYOUT] = $layout;
        $this->config[$code][self::KEY_TEMPLATE] = $tplPath;
        $this->config[$code][self::KEY_SPA] = $enableSpa;
        return $this;
    }

    /**
     * Assign layout to theme
     *  eg 'admin' => 'MobileCartAdminBundle::admin-layout.html.twig'
     *  eg 'frontend' => 'MobileCartCoreBundle::frontend-layout.html.twig'
     *
     * @param $code
     * @param $layout
     * @return $this
     */
    public function setThemeLayout($code, $layout)
    {
        $this->config[$code][self::KEY_LAYOUT] = $layout;
        return $this;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getThemeLayout($code)
    {
        return $this->config[$code][self::KEY_LAYOUT];
    }

    /**
     *  Set the template path eg
     *  eg
     *   'MobileCartCoreBundle:Admin/'
     *   for use in path strings :
     *   "MobileCartCoreBundle:Admin/Discount:index.html.twig"
     *
     * @param $code
     * @param $relPath
     * @return $this
     */
    public function setTemplatePath($code, $relPath)
    {
        $this->config[$code][self::KEY_TEMPLATE] = $relPath;
        return $this;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getTemplatePath($code)
    {
        return $this->config[$code][self::KEY_TEMPLATE];
    }

    /**
     * @param $theme
     * @return $this
     */
    public function setFrontendTheme($theme)
    {
        $this->frontendTheme = $theme;
        return $this;
    }

    /**
     * Get theme string
     *  in the future, this could look at SERVER hostname
     *  and return different values
     *
     * @return string
     */
    public function getFrontendTheme()
    {
        return $this->frontendTheme;
    }

    /**
     * @param $theme
     * @return $this
     */
    public function setAdminTheme($theme)
    {
        $this->adminTheme = $theme;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdminTheme()
    {
        return $this->adminTheme;
    }

    /**
     * @param $code
     * @param $yesNo
     * @return $this
     */
    public function setIsSpaEnabled($code, $yesNo)
    {
        $this->config[$code][self::KEY_SPA] = $yesNo;
        return $this;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getIsSpaEnabled($code)
    {
        return $this->config[$code][self::KEY_SPA];
    }

    /**
     * @param $objectType
     * @param $route
     * @returns $this
     */
    public function addAdminEditRoute($objectType, $route)
    {
        $this->adminEditRoutes[$objectType] = $route;
        return $this;
    }

    /**
     * @param $objectType
     * @return string
     */
    public function getAdminEditRoute($objectType)
    {
        return isset($this->adminEditRoutes[$objectType])
            ? $this->adminEditRoutes[$objectType]
            : '';
    }

    /**
     * @param $objectType
     * @param $template
     * @param $name
     * @return $this
     */
    public function setObjectTypeTemplate($objectType, $template, $name)
    {
        if (!is_array($this->objectTypeTemplates[$objectType])) {
            $this->objectTypeTemplates[$objectType] = [];
        }

        $this->objectTypeTemplates[$objectType][$template] = $name;

        return $this;
    }

    /**
     * @param $objectType
     * @return array
     */
    public function getObjectTypeTemplates($objectType)
    {
        $customTemplates = (isset($this->objectTypeTemplates[$objectType]) && is_array($this->objectTypeTemplates[$objectType]))
            ? $this->objectTypeTemplates[$objectType]
            : [];

        return array_merge(
            ['' => 'Default'],
            $customTemplates
        );
    }
}
