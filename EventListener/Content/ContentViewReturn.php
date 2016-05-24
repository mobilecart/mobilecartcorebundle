<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use Symfony\Component\EventDispatcher\Event;

class ContentViewReturn
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
     * @return mixed
     */
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

    public function onContentViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $returnData['entity'] = $event->getEntity();

        $customTpl = $event->getCustomTemplate();
        if (!$customTpl && $event->getEntity()->getCustomTemplate()) {
            $customTpl = $event->getEntity()->getCustomTemplate();
        }

        $template = $customTpl
            ? $customTpl
            : 'Content:view.html.twig';

        $response = $this->getThemeService()
            ->render('frontend', $template, $returnData);

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
