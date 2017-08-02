<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerForgotPasswordReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerForgotPasswordReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
    public function onCustomerForgotPasswordReturn(CoreEvent $event)
    {
        $form = $event->getReturnData('form');
        $form = $form->createView();
        $event->setReturnData('form', $form);

        $event->setResponse($this->getThemeService()->render(
            'frontend',
            'Customer:forgot_password.html.twig',
            $event->getReturnData()
        ));
    }
}
