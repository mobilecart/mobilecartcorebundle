<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Total;

/**
 * Class ItemTotal
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ItemTotal extends Total
{
    const KEY = 'items';
    const LABEL = 'Items';

    /**
     * @param CoreEvent $event
     */
    public function onCartTotalCollect(CoreEvent $event)
    {
        $itemTotal = $event->getCart()
            ->getCalculator()
            ->getItemTotal();

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($itemTotal)
            ->setIsAdd(1);

        $event->addTotal($this);
    }
}
