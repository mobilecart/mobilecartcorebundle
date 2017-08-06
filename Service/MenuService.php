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
    protected $eventDispatcher;

    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param $alias
     * @return \Knp\Menu\MenuItem
     */
    public function createMenu($alias)
    {
        $menu = $this->createItem($alias);

        $event = new CoreEvent();
        $event->set('menu', $menu);

        // dispatch event
        $this->getEventDispatcher()
            ->dispatch(CoreEvents::MENU_BUILD . '.' . $alias, $event);

        return $event->get('menu');
    }
}
