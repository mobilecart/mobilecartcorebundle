<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class ItemTotal extends Total
{
    const KEY = 'items';
    const LABEL = 'Items';

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
    public function onCartTotalCollect(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $itemTotal = $event->getCart()->getCalculator()
            ->getItemTotal();

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($itemTotal)
            ->setIsAdd(1);

        $event->addTotal($this);
        $event->setReturnData($returnData);
    }
}
