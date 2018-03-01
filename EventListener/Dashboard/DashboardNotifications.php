<?php

namespace MobileCart\CoreBundle\EventListener\Dashboard;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class DashboardNotifications
 * @package MobileCart\CoreBundle\EventListener\Dashboard
 */
class DashboardNotifications
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
        $event->setReturnData('notifications', [
            'section_id'   => 'notifications',
            'label'        => 'Notifications',
            'template'     => $this->getThemeService()->getAdminTemplatePath() . ':' . 'Dashboard/notifications.html.twig',
        ]);

        $event->setReturnData('template_sections', []);
    }
}
