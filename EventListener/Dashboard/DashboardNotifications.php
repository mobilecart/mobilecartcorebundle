<?php

namespace MobileCart\CoreBundle\EventListener\Dashboard;

use Symfony\Component\EventDispatcher\Event;

class DashboardNotifications
{
    protected $entityService;

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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function onDashboardViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $sections = [];

        $sections['notifications'] = [
            'section_id'   => 'notifications',
            'label'        => 'Notifications',
            'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Dashboard/notifications.html.twig',
        ];

        $returnData['template_sections'] = $sections;

        $event->setReturnData($returnData);
    }
}
