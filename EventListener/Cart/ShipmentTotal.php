<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class ShipmentTotal extends Total
{
    const KEY = 'shipments';
    const LABEL = 'Shipments';

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
     * @return bool
     */
    public function onCartTotalCollect(Event $event)
    {
        if (!$event->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $shipmentTotal = $event->getCart()->getCalculator()
            ->getShipmentTotal();

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($shipmentTotal)
            ->setIsAdd(1);

        $event->addTotal($this);

        $event->setReturnData($returnData);
    }
}
