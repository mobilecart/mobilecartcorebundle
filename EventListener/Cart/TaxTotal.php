<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class TaxTotal extends Total
{
    const KEY = 'tax';
    const LABEL = 'Tax';

    protected $event;

    protected $taxService;

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

    public function setTaxService($taxService)
    {
        $this->taxService = $taxService;
        return $this;
    }

    public function getTaxService()
    {
        return $this->taxService;
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function onCartTotalCollect(Event $event)
    {
        if (!$event->getIsTaxEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $cart = $event->getCart();
        $currency = $cart->getCurrency() ? $cart->getCurrency() : 'USD';
        $countryId = $cart->getCustomer()->getBillingCountryId();
        $billingRegion = $cart->getCustomer()->getBillingRegion();

        $rate = $this->getTaxService()->getRate($currency, $countryId, $billingRegion);
        if ($rate !== false) {
            $cart->setTaxRate($rate);
        }

        $taxTotal = $event->getCart()->getCalculator()
            ->getTaxTotal();

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($taxTotal)
            ->setIsAdd(1);

        $event->addTotal($this);

        $event->setReturnData($returnData);
    }
}
