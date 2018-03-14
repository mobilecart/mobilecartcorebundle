<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarOptionSearch
 * @package MobileCart\CoreBundle\EventListener\ItemVarOption
 */
class ItemVarOptionSearch
{
    /**
     * @var \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    protected $search;

    /**
     * @param \MobileCart\CoreBundle\Service\SearchServiceInterface $search
     * @return $this
     */
    public function setSearch(\MobileCart\CoreBundle\Service\SearchServiceInterface $search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param CoreEvent $event
     */
    public function onItemVarOptionSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $this->getSearch()
            ->setObjectType($event->getObjectType())
            ->parseRequest($event->getRequest())
            ->addJoin('inner', 'item_var', 'id', 'item_var_id')
            ->addColumn('item_var.name', 'item_var_name');

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search()->getResult());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_item_var_option', $request->getQueryString());
        }
    }
}
