<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerNewReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerNewReturn
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
    public function onCustomerNewReturn(CoreEvent $event)
    {
        $event->setReturnData('template_sections', []);
        $event->setReturnData('form', $event->getReturnData('form')->createView());
        $event->setReturnData('entity', $event->getEntity());

        if ($event->getRequest()->getSession() && $event->getMessages()) {
            foreach($event->getMessages() as $code => $messages) {
                if (!$messages) {
                    continue;
                }
                foreach($messages as $message) {
                    $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                }
            }
        }

        $event->setResponse($this->getThemeService()->render(
            'admin',
            'Customer:new.html.twig',
            $event->getReturnData()
        ));
    }
}
