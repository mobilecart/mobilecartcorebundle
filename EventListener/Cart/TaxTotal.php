<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class TaxTotal extends Total
{
    const KEY = 'tax';
    const LABEL = 'Tax';

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var \MobileCart\CoreBundle\Service\TaxService
     */
    protected $taxService;

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
     * @param $taxService
     * @return $this
     */
    public function setTaxService($taxService)
    {
        $this->taxService = $taxService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\TaxService
     */
    public function getTaxService()
    {
        return $this->taxService;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function onCartTotalCollect(Event $event)
    {
        if (!$event->getIsTaxEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $cart = $event->getCart();
        $cart->setIncludeTax(1);
        $currency = $cart->getCurrency() ? $cart->getCurrency() : 'USD';
        $countryId = $cart->getCustomer()->getBillingCountryId();
        $billingRegion = $cart->getCustomer()->getBillingRegion();

        $rate = $this->getTaxService()->getRate($currency, $countryId, $billingRegion);
        if ($rate !== false) {
            $cart->setTaxRate($rate['rate']);
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
