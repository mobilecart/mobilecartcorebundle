<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerAddressEditReturn
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressEditReturn
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
     * @param CoreEvent $event
     */
    public function onCustomerAddressEditReturn(CoreEvent $event)
    {
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('entity', $event->getEntity());
        $event->setReturnData('template_sections', []);

        $event->flashMessages();

        $event->setResponse($this->getThemeService()->render(
            'frontend',
            'CustomerAddress:edit.html.twig',
            $event->getReturnData()
        ));
    }
}
