<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class GrandTotal extends Total
{
    const KEY = 'grand_total';
    const LABEL = 'Grand Total';

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
        $this->setEvent($event);
        $returnData = $this->getReturnData();

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

        $event->setReturnData($returnData);
    }
}
