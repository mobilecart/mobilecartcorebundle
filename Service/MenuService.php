<?php

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class MenuService
 * @package MobileCart\CoreBundle\Service
 */
class MenuService extends \Knp\Menu\MenuFactory
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param $alias
     * @param array $options
     * @return \Knp\Menu\MenuItem
     */
    public function createMenu($alias, array $options = [])
    {
        $menu = $this->createItem($alias, $options);

        $event = new CoreEvent();
        $event->set('menu', $menu);

        // dispatch event
        $this->getEventDispatcher()
            ->dispatch(CoreEvents::MENU_BUILD . '.' . $alias, $event);

        return $event->get('menu');
    }
}
