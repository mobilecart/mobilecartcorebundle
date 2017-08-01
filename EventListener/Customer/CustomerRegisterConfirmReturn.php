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
        $returnData = $event->getReturnData();
        $objectType = $event->getObjectType();
        $entity = $event->getEntity();

        $typeSections = [];

        $returnData['template_sections'] = $typeSections;

        $tpl = $event->getSuccess()
            ? 'Customer:register_confirm_success.html.twig'
            : 'Customer:register_confirm_error.html.twig';

        if ($event->getSuccess()) {
            $returnData = array_merge($returnData, $entity->getData());
        }

        if ($messages = $event->getMessages()) {
            foreach($messages as $code => $message) {
                $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
            }
        }

        $response = $this->getThemeService()
            ->render('frontend', $tpl, $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
