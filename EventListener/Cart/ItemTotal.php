<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class ItemTotal extends Total
{
    const KEY = 'items';
    const LABEL = 'Items';

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
