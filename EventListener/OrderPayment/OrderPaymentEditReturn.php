<?php

namespace MobileCart\CoreBundle\EventListener\OrderPayment;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderPaymentEditReturn
 * @package MobileCart\CoreBundle\EventListener\OrderPayment
 */
class OrderPaymentEditReturn
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
    public function onOrderPaymentEditReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $event->setReturnData('entity', $entity);
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('template_sections', []);

        $event->setResponse($this->getThemeService()->render(
            'admin',
            'OrderPayment:edit.html.twig',
            $event->getReturnData()
        ));
    }
}
