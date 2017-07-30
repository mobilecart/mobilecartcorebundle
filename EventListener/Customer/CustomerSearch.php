<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerSearch
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onCustomerSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->setDefaultSort('created_at', 'desc')
            ->parseRequest($request);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_customer', $request->getQueryString());
        }
    }
}
