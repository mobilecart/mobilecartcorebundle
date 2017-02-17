<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

class TaxService
{
    /**
     * @var bool
     */
    protected $isTaxEnabled = true;

    /**
     * @var array
     */
    protected $rates = [];

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsTaxEnabled($yesNo)
    {
        // handle XML strings
        $isEnabled = ($yesNo != '0' && $yesNo != 'false');
        $this->isTaxEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsTaxEnabled()
    {
        return $this->isTaxEnabled;
    }

    /**
     * @param $currency
     * @param $countryId
     * @param $region
     * @param $taxRate
     * @param string $postcode
     * @return $this
     */
    public function addRate($currency, $countryId, $region, $taxRate, $postcode = '')
    {
        if ($postcode) {
            $this->rates[$currency][$countryId][$region][$postcode] = [
                'rate' => $taxRate,
                'currency' => $currency,
                'postcode' => $postcode,
            ];
        } else {
            $this->rates[$currency][$countryId][$region] = [
                'rate' => $taxRate,
                'currency' => $currency,
                'postcode' => $postcode,
            ];
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param $currency
     * @param $countryId
     * @param $region
     * @param string $postcode
     * @return bool
     */
    public function getRate($currency, $countryId, $region, $postcode = '')
    {
        if (!isset($this->rates[$currency][$countryId][$region])) {
            return false;
        }

        if ($postcode) {
            // todo : handle ranges and wildcards
            return $this->rates[$currency][$countryId][$region];
        } else {
            return $this->rates[$currency][$countryId][$region];
        }
    }

    /**
     * @param $currency
     * @param $countryId
     * @param $region
     * @param string $postcode
     * @return float
     */
    public function getMultiplier($currency, $countryId, $region, $postcode = '')
    {
        $rate = $this->getRate($currency, $countryId, $region, $postcode);
        if ($rate === false) {
            return 0; // no tax rate
        }

        $hasPeriod = is_int(strpos($rate, '.'));
        $hasComma = is_int(strpos($rate, ','));

        // if we couldn't figure it out, return a non-effect multiplier
        if (!$hasComma && !$hasPeriod) {
            return 0;
        }

        $delimiter = $hasComma
            ? ','
            : '.';

        $parts = explode($delimiter, $rate);
        $decimal = isset($parts[1])
            ? $parts[1]
            : 0;

        $multiplier = 0 + $decimal;

        return $multiplier;
    }
}
