<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;

class CustomerRegisterConfirmReturn
{
    protected $request;

    protected $entityService;

    protected $themeService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function onCustomerRegisterConfirmReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

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

        $event->setResponse($response);
        $event->setReturnData($returnData);
    }
}
