<?php

namespace MobileCart\CoreBundle\EventListener\Dashboard;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class DashboardViewReturn
 * @package MobileCart\CoreBundle\EventListener\Dashboard
 */
class DashboardViewReturn
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
     * @param CoreEvent $event
     */
    public function onDashboardViewReturn(CoreEvent $event)
    {
        if (is_null($event->getReturnData('template_sections'))) {
            $event->setReturnData('template_sections', []);
        }

        $event->setResponse($this->getThemeService()->renderAdmin(
            'Dashboard:index.html.twig',
            $event->getReturnData()
        ));
    }
}
