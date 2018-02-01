<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerRegisterConfirmReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterConfirmReturn
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
    public function onCustomerRegisterConfirmReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $event->setReturnData('template_sections', []);

        $tpl = 'Customer:register_confirm_error.html.twig';
        if ($event->getReturnData('success')) {
            $tpl = 'Customer:register_confirm_success.html.twig';
            $event->addReturnData($entity->getData());
        }

        $event->flashMessages();

        $event->setResponse($this->getThemeService()->render(
            'frontend',
            $tpl,
            $event->getReturnData()
        ));
    }
}
