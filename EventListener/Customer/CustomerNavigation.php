<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerNavigation
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerNavigation
{
    protected $router;

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
     * @param CoreEvent $event
     */
    public function onCustomerNavigation(CoreEvent $event)
    {
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
