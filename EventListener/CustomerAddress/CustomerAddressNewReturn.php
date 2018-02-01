<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerAddressNewReturn
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressNewReturn
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
    public function onCustomerAddressNewReturn(CoreEvent $event)
    {
        $event->setReturnData('entity', $event->getEntity());
        $event->setReturnData('template_sections', []);
        $event->setReturnData('form', $event->getForm()->createView());

        $event->flashMessages();

        $event->setResponse($this->getThemeService()->render(
            'frontend',
            'CustomerAddress:new.html.twig',
            $event->getReturnData()
        ));
    }
}
