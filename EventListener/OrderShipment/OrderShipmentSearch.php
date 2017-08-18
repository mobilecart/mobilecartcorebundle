<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderShipmentSearch
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onOrderShipmentSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->setDefaultSort('created_at', 'desc')
            ->parseRequest($request);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_order_shipment', $request->getQueryString());
        }
    }
}
