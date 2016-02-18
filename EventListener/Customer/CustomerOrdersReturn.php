<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CustomerOrdersReturn
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

    public function onCustomerOrdersReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $customer = $event->getCustomer();

        $objectType = $event->getObjectType();

        $typeSections = [];

        $returnData['template_sections'] = $typeSections;

        $orders = $this->getEntityService()->findBy(EntityConstants::ORDER,[
            'customer' => $customer->getId(),
        ]);

        $returnData['orders'] = $orders;

        $response = $this->getThemeService()
            ->render('frontend', 'Customer:orders.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
