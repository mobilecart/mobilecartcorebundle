<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerEditReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerEditReturn
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
    public function onCustomerEditReturn(CoreEvent $event)
    {
        $event->setReturnData('form', $event->getReturnData('form')->createView());
        $event->setReturnData('entity', $event->getEntity());
        $event->setReturnData('template_sections', []);

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
            'Customer:edit.html.twig',
            $event->getReturnData()
        ));
    }
}
