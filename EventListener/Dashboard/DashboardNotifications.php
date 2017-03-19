<?php

namespace MobileCart\CoreBundle\EventListener\Dashboard;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DashboardNotifications
 * @package MobileCart\CoreBundle\EventListener\Dashboard
 */
class DashboardNotifications
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
     * @param Event $event
     */
    public function onDashboardViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $sections = [];

        $sections['notifications'] = [
            'section_id'   => 'notifications',
            'label'        => 'Notifications',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . ':' . 'Dashboard/notifications.html.twig',
        ];

        $returnData['template_sections'] = $sections;

        $event->setReturnData($returnData);
    }
}
