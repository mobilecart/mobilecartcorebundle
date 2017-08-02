<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerRegisterCheckEmailReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterCheckEmailReturn
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
    public function onCustomerRegisterCheckEmailReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $typeSections = [];
        $returnData['template_sections'] = $typeSections;

        if ($codeMessages = $event->getMessages()) {
            foreach($codeMessages as $code => $messages) {
                if (!$messages) {
                    continue;
                }
                foreach($messages as $message) {
                    $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                }
            }
        }

        $response = $this->getThemeService()
            ->render('frontend', 'Customer:register_check_email.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
