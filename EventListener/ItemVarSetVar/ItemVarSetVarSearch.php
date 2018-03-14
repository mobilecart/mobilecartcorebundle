<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSetVar;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarSetVarSearch
 * @package MobileCart\CoreBundle\EventListener\ItemVarSetVar
 */
class ItemVarSetVarSearch
{
    /**
     * @var \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    protected $search;

    /**
     * @param \MobileCart\CoreBundle\Service\SearchServiceInterface $search
     * @param $objectType
     * @return $this
     */
    public function setSearch(\MobileCart\CoreBundle\Service\SearchServiceInterface $search, $objectType)
    {
        $this->search = $search->setObjectType($objectType);
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
    public function onItemVarSetVarSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $this->getSearch()
            ->parseRequest($request)
            ->addJoin('inner', 'item_var', 'id', 'item_var_id')
            ->addColumn('item_var.name', 'item_var_name')
            ->addJoin('inner', 'item_var_set', 'id', 'item_var_set_id')
            ->addColumn('item_var_set.name', 'item_var_set_name');

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search()->getResult());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_item_var_set_var', $request->getQueryString());
        }
    }
}
