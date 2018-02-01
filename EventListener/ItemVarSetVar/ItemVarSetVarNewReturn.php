<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSetVar;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarSetVarNewReturn
 * @package MobileCart\CoreBundle\EventListener\ItemVarSetVar
 */
class ItemVarSetVarNewReturn
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
     * @param CoreEvent $event
     */
    public function onItemVarSetVarNewReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $event->setReturnData('entity', $entity);
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('template_sections', []);

        $event->setResponse($this->getThemeService()->render(
            'admin',
            'ItemVarSetVar:new.html.twig',
            $event->getReturnData()
        ));
    }
}
