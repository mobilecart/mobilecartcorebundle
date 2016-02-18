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

use Symfony\Component\HttpFoundation\Response;

class ThemeService
{
    /**
     * @var
     */
    protected $themeConfig;

    /**
     * @var
     */
    protected $templating;

    public function setThemeConfig($themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * @param $templating
     * @return $this
     */
    public function setTemplating($templating)
    {
        $this->templating = $templating;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplating()
    {
        return $this->templating;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getTemplatePath($code)
    {
        return $this->getThemeConfig()->getTemplatePath($code);
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getThemeLayout($code)
    {
        return $this->getThemeConfig()->getThemeLayout($code);
    }

    /**
     * Render and return Response
     *
     * @param $theme
     * @param $template
     * @param $data
     * @param Response $response
     * @return Response
     */
    public function render($theme, $template, $data, Response $response = null)
    {
        return $this->getTemplating()
            ->renderResponse($this->getThemeConfig()->getTemplatePath($theme) . $template, $data, $response);
    }

    /**
     * Render and return HTML
     *
     * @param $theme
     * @param $template
     * @param $data
     * @return string
     */
    public function renderView($theme, $template, $data)
    {
        return $this->getTemplating()
            ->render($this->getThemeConfig()->getTemplatePath($theme) . $template, $data);
    }
}
