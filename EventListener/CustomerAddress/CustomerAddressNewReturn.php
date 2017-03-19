<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CustomerAddressNewReturn
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressNewReturn
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
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
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

    public function onCustomerAddressNewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $objectType = $event->getObjectType();

        $typeSections = [];
        $returnData['template_sections'] = $typeSections;

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $entity;

        if ($messages = $event->getMessages()) {
            foreach($messages as $code => $message) {
                $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
            }
        }

        $response = $this->getThemeService()
            ->render('frontend', 'CustomerAddress:new.html.twig', $returnData);

        $event->setResponse($response);
        $event->setReturnData($returnData);
    }
}
