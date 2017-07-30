<?php

namespace MobileCart\CoreBundle\EventListener\OrderItem;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderItemSearch
 * @package MobileCart\CoreBundle\EventListener\OrderItem
 */
class OrderItemSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onOrderItemSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->setDefaultSort('id', 'desc')
            ->parseRequest($request)
            ->addJoin('inner', 'order_sale', 'id', 'order_id')
            ->addColumn('order_sale.reference_nbr', 'reference_nbr')
            ->addColumn('order_sale.created_at', 'order_created_at')
            ->addJoin('left', 'order_shipment', 'id', 'order_shipment_id')
            ->addColumn('order_shipment.method', 'shipping_method')
            ->addSortable([
                'reference_nbr' => 'Order Reference',
                'order_created_at' => 'Order Timestamp',
                'shipping_method' => 'Shipping Method',
            ]);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_order_item', $request->getQueryString());
        }
    }
}
