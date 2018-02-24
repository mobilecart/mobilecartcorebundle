<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ShippingMethodEditReturn
 * @package MobileCart\CoreBundle\EventListener\ShippingMethod
 */
class ShippingMethodEditReturn
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
    public function onShippingMethodEditReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();
        $returnData['template_sections'] = [];

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $entity;

        $response = $this->getThemeService()
            ->render('admin', 'ShippingMethod:edit.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
