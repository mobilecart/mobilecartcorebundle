<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Total;

/**
 * Class TaxTotal
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class TaxTotal extends Total
{
    const KEY = 'tax';
    const LABEL = 'Tax';

    /**
     * @var \MobileCart\CoreBundle\Service\TaxService
     */
    protected $taxService;

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
     * @param CoreEvent $event
     * @return bool
     */
    public function onCartTotalCollect(CoreEvent $event)
    {
        if (!$event->getIsTaxEnabled()) {
            return false;
        }

        $cart = $event->getCart();
        $cart->setIncludeTax(1);
        $currency = $cart->getCurrency() ? $cart->getCurrency() : 'USD';
        $countryId = $cart->getCustomer()->getBillingCountryId();
        $region = $cart->getCustomer()->getBillingRegion();

        if (strlen($cart->getCustomer()->getShippingRegion())
            && strlen($cart->getCustomer()->getShippingCountryId())
        ) {
            $countryId = $cart->getCustomer()->getShippingCountryId();
            $region = $cart->getCustomer()->getShippingRegion();
        }

        $rate = $this->getTaxService()->getRate($currency, $countryId, $region);
        if ($rate !== false) {
            $cart->setTaxRate($rate['rate']);
        } else {
            $cart->setTaxRate(0);
        }

        $taxTotal = $event->getCart()->getCalculator()
            ->getTaxTotal();

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($taxTotal)
            ->setIsAdd(1);

        $event->addTotal($this);
    }
}
