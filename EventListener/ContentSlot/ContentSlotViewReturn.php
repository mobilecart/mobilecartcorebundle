<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use Symfony\Component\EventDispatcher\Event;

class ContentSlotViewReturn
{
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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function onContentSlotViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $customTpl = $event->getCustomTemplate();
        if (!$customTpl && $event->getEntity()->getCustomTemplate()) {
            $customTpl = $event->getEntity()->getCustomTemplate();
        }

        $template = $customTpl
            ? $customTpl
            : 'Content:view.html.twig';

        //$response = $this->getThemeService()
        //    ->render('frontend', $template, $returnData);

        $response = null;

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
