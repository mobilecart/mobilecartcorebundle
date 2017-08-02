<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerRegisterReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterReturn
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
    public function onCustomerRegisterReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $typeSections = [];
        $returnData['template_sections'] = $typeSections;

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();

        if ($messages = $event->getMessages()) {
            foreach($messages as $code => $message) {
                $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
            }
        }

        $response = $this->getThemeService()
            ->render('frontend', 'Customer:register.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
