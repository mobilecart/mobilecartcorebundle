<?php

namespace MobileCart\CoreBundle\EventListener\Admin;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class AdminNavigation
 * @package MobileCart\CoreBundle\EventListener\Admin
 */
class AdminNavigation
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
    public function onAdminNavigation(CoreEvent $event)
    {
        $event->get('menu')
            ->setChildrenAttribute('class', 'nav')
            ->setChildrenAttribute('id', 'side-menu');

        // NOTE : This is meant to be a simple example

        $event->get('menu')->addChild('<i class="fa fa-gears fa-fw"></i> Config Settings', [
            'route' => 'cart_admin_config_setting',
            'uri'   => $this->getRouter()->generate('cart_admin_dashboard', []),
            'extras' => [
                'safe_label' => true,
            ]
        ]);
    }
}
