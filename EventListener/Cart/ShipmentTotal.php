<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Total;

/**
 * Class ShipmentTotal
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ShipmentTotal extends Total
{
    const KEY = 'shipments';
    const LABEL = 'Shipments';

    /**
     * @param CoreEvent $event
     * @return bool
     */
    public function onCartTotalCollect(CoreEvent $event)
    {
        if (!$event->getIsShippingEnabled()) {
            return false;
        }

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
