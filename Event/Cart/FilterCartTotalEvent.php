<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Event\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Cart;
use MobileCart\CoreBundle\CartComponent\Total;
use MobileCart\CoreBundle\Service\CurrencyService;

class FilterCartTotalEvent extends Event
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var CurrencyService
     */
    protected $currencyService;

    /**
     * @var array
     */
    protected $totals = []; // r[key] = Total object

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
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
     * @param Total $total
     * @return $this
     */
    public function addTotal(Total $total)
    {
        $this->totals[$total->getKey()] = $total;
        return $this;
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        return $this->totals;
    }
}
