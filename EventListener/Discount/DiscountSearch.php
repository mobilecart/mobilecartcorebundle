<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class DiscountSearch
 * @package MobileCart\CoreBundle\EventListener\Discount
 */
class DiscountSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onDiscountSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->parseRequest($event->getRequest());

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_discount', $request->getQueryString());
        }
    }
}
