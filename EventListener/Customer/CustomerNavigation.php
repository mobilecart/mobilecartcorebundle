<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;

class CustomerNavigation
{
    protected $router;

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param Event $event
     */
    public function onCustomerNavigation(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

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
