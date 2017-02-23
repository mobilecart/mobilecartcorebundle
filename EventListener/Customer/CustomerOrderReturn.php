<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CustomerOrderReturn
{
    protected $entityService;

    protected $themeService;

    protected $event;

    protected $router;

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
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

    public function onCustomerOrderReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();
        $orderId = $request->get('id', 0);

        $customer = $event->getCustomer();

        $objectType = $event->getObjectType();

        $typeSections = [];

        $returnData['template_sections'] = $typeSections;

        $order = $this->getEntityService()->find(EntityConstants::ORDER, $orderId);
        if (!$order
            || !$order->getCustomer()
            || !$order->getCustomer()->getId()
            || $order->getCustomer()->getId() != $customer->getId()
        ) {
            // redirect to order listing
            $url = $this->getRouter()->generate('customer_orders', []);
            $event->setResponse(new RedirectResponse($url));
            return;
        }

        $returnData['order'] = $order;

        $response = $this->getThemeService()
            ->render('frontend', 'Customer:order.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
