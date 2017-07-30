<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSet;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarSetSearch
 * @package MobileCart\CoreBundle\EventListener\ItemVarSet
 */
class ItemVarSetSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onItemVarSetSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->parseRequest($request);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_item_var_set', $request->getQueryString());
        }
    }
}
