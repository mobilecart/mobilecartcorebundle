<?php

namespace MobileCart\CoreBundle\EventListener\UrlRewrite;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class UrlRewriteEditReturn
 * @package MobileCart\CoreBundle\EventListener\UrlRewrite
 */
class UrlRewriteEditReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

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
     * @param CoreEvent $event
     */
    public function onUrlRewriteEditReturn(CoreEvent $event)
    {
        $event->setReturnData('entity', $event->getEntity());
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('template_sections', []);

        $event->setResponse($this->getThemeService()->renderAdmin(
            'UrlRewrite:edit.html.twig',
            $event->getReturnData()
        ));
    }
}
