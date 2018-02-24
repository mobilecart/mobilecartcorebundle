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

use Symfony\Component\Intl\Intl;

/**
 * Interface CurrencyServiceInterface
 * @package MobileCart\CoreBundle\Service
 */
interface CurrencyServiceInterface
{
    // todo: integrate more of:  http://symfony.com/doc/current/components/intl.html

    /**
     * @var string keys for displayMap data
     */
    const DEC_POINT = 'dec_point';
    const THOUSANDS_SEP = 'thousands_sep';
    const SYMBOL = 'symbol';
    const BEFORE_AFTER = 'before_after';
    const DISPLAY_PRECISION = 'display_precision';
    const MULTIPLY_PRECISION = 'multiply_precision';

    /**
     * @param $to
     * @return bool
     */
    public function hasRate($to);

    /**
     * @param $fromTo
     * @param $multiplier
     * @param int $multiplyPrecision
     * @param string $decPoint
     * @param string $thousandsSep
     * @param $beforeAfter
     * @return $this
     */
    public function addRate($fromTo, $multiplier, $multiplyPrecision = 4, $decPoint = '.', $thousandsSep = ',', $beforeAfter = -1);

    /**
     * @param $currency
     * @return string
     */
    public function getSymbol($currency);

    /**
     * @param $code
     * @return $this
     */
    public function setBaseCurrency($code);

    /**
     * @return string
     */
    public function getBaseCurrency();

    /**
     * @return string
     */
    public function getBaseSymbol();

    /**
     * @return int|null
     */
    public function getBaseDisplayedPrecision();

    /**
     * @param $currency
     * @return int|null
     */
    public function getDisplayedPrecision($currency);

    /**
     * @param $precision
     * @return $this
     */
    public function setBaseMultiplierPrecision($precision);

    /**
     * @param $decPoint
     * @return $this
     */
    public function setBaseDecimalPoint($decPoint);

    /**
     * @param $sep
     */
    public function setBaseThousandsSep($sep);

    /**
     * @param $fromTo
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function quote($fromTo);

    /**
     * Handy proxy method
     *  maybe easier in templates; via a Twig Extension
     *
     * @param $from
     * @param $to
     * @return mixed
     */
    public function getRate($from, $to);

    /**
     * @param $from
     * @param $to
     * @param $value
     * @return string
     */
    public function convert($value, $to = '', $from = '');

    /**
     * @param $to
     * @param $value
     * @param string $from
     * @return string
     */
    public function decorate($value, $to = '', $from = '');
}
