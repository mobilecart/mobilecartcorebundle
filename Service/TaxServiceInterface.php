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

/**
 * Interface TaxServiceInterface
 * @package MobileCart\CoreBundle\Service
 */
interface TaxServiceInterface
{
    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsTaxEnabled($yesNo);

    /**
     * @return bool
     */
    public function getIsTaxEnabled();

    /**
     * @param $currency
     * @param $countryId
     * @param $region
     * @param $taxRate
     * @param string $postcode
     * @return $this
     */
    public function addRate($currency, $countryId, $region, $taxRate, $postcode = '');

    /**
     * @return array
     */
    public function getRates();

    /**
     * @param $currency
     * @param $countryId
     * @param $region
     * @param string $postcode
     * @return bool
     */
    public function getRate($currency, $countryId, $region, $postcode = '');

    /**
     * @param $currency
     * @param $countryId
     * @param $region
     * @param string $postcode
     * @return float
     */
    public function getMultiplier($currency, $countryId, $region, $postcode = '');
}
