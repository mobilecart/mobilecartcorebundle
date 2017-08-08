<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Total;

/**
 * Class GrandTotal
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class GrandTotal extends Total
{
    const KEY = 'grand_total';
    const LABEL = 'Grand Total';

    /**
     * @param CoreEvent $event
     */
    public function onCartTotalCollect(CoreEvent $event)
    {
        $grandTotal = 0;
        if ($event->getTotals()) {
            foreach($event->getTotals() as $total) {
                if ($total->getIsAdd()) {
                    $grandTotal += $total->getValue();
                } else {
                    $grandTotal -= $total->getValue();
                }
            }
        }

        // little extra precaution
        if ($grandTotal < 0) {
            $grandTotal = 0;
        }

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($grandTotal)
            ->setIsAdd(0); // subtract

        $event->addTotal($this);
    }
}
