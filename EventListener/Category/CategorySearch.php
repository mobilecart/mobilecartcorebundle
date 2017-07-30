<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CategorySearch
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategorySearch
{
    /**
     * @param CoreEvent $event
     */
    public function onCategorySearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->parseRequest($request);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            $search->setDefaultSort('sort_order', 'asc');
        }

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_category', $request->getQueryString());
        }
    }
}
