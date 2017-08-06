<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerNavigation
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerNavigation
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerNavigation(CoreEvent $event)
    {
        $event->get('menu')->setChildrenAttribute('class', 'side-menu nav');

        $event->get('menu')->addChild('My Account', [
            'route' => 'customer_profile',
            'uri'   => $this->getRouter()->generate('customer_profile', []),
        ]);

        $event->get('menu')->addChild('Addresses', [
            'route' => 'customer_addresses',
            'uri'   => $this->getRouter()->generate('customer_addresses', []),
        ]);

        $event->get('menu')->addChild('Orders', [
            'route' => 'customer_orders',
            'uri'   => $this->getRouter()->generate('customer_orders', []),
        ]);
    }
}
