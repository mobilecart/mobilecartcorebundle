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
    public function onCustomerAddressNewReturn(CoreEvent $event)
    {
        $event->setReturnData('entity', $event->getEntity());
        $event->setReturnData('template_sections', []);
        $event->setReturnData('form', $event->getForm()->createView());

        $event->flashMessages();

        if ($event->getSection() == CoreEvent::SECTION_BACKEND) {

            $event->setResponse($this->getThemeService()->renderAdmin(
                'CustomerAddress:new.html.twig',
                $event->getReturnData()
            ));

        } else {

            $event->setResponse($this->getThemeService()->renderFrontend(
                'CustomerAddress:new.html.twig',
                $event->getReturnData()
            ));

        }
    }
}
