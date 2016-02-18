<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class ShipmentTotal extends Total
{
    const KEY = 'shipments';
    const LABEL = 'Shipments';

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function onCartTotalCollect(Event $event)
    {
        if (!$event->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

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
