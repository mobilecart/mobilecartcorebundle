<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderShipmentEditReturn
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentEditReturn
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
    public function onOrderShipmentEditReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();

        $returnData['template_sections'] = [];
        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $entity;

        $response = $this->getThemeService()
            ->render('admin', 'OrderShipment:edit.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
