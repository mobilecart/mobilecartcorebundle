<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;

class CustomerNavigation
{
    protected $router;

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function onCustomerNavigation(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $returnData['navigation'] = [
            'customer_profile' => [
                'label' => 'Customer Account',
                'url'   => $this->getRouter()->generate('customer_profile', []),
            ],
            'customer_addresses' => [
                'label' => 'Shipping Addresses',
                'url'   => $this->getRouter()->generate('customer_addresses', []),
            ],
            'customer_orders' => [
                'label' => 'Orders',
                'url'   => $this->getRouter()->generate('customer_orders', []),
            ],
        ];

        if ($active = $event->getCurrentRoute()) {
            $returnData[$active]['active'] = 1;
        }

        $event->setReturnData($returnData);
    }
}
