<?php

namespace MobileCart\CoreBundle\EventListener\OrderItem;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class OrderItemSearch
 * @package MobileCart\CoreBundle\EventListener\OrderItem
 */
class OrderItemSearch
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function onOrderItemSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->setDefaultSort('id', 'desc')
            ->parseRequest($event->getRequest())
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

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        $event->setReturnData($returnData);

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_order_item', $request->getQueryString());
        }
    }
}
