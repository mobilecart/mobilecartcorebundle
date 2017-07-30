<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ContentSlotSearch
 * @package MobileCart\CoreBundle\EventListener\ContentSlot
 */
class ContentSlotSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onContentSlotSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->parseRequest($request);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_content_slot', $request->getQueryString());
        }
    }
}
