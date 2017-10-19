<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Total;
use MobileCart\CoreBundle\Service\TaxService;
use MobileCart\CoreBundle\Service\CurrencyService;

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
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @param \MobileCart\CoreBundle\Service\TaxService $taxService
     * @return $this
     */
    public function setTaxService(TaxService $taxService)
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
     * @param CurrencyService $currencyService
     * @return $this
     */
    public function setCurrencyService(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @param CoreEvent $event
     * @return bool
     */
    public function onCartTotalCollect(CoreEvent $event)
    {
        if (!$this->getTaxService()->getIsTaxEnabled()) {
            return false;
        }

        // if tax is enabled and shipping is disabled, then apply tax to billing

        $cart = $event->getCart();
        $cart->setIncludeTax(1);

        $currency = $cart->getCurrency()
            ? $cart->getCurrency()
            : $this->getCurrencyService()->getBaseCurrency();

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
