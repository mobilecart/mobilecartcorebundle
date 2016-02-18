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

use MobileCart\CoreBundle\CartComponent\Cart;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

class CartTotalService
{
    /**
     * @var bool
     */
    protected $isShippingEnabled = true;

    /**
     * @var bool
     */
    protected $isTaxEnabled = true;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var array
     */
    protected $totals; // r[key] = Total object

    /**
     * @var bool
     */
    protected $applyAutoDiscounts = false;

    /**
     * @var array
     */
    protected $excludeDiscountIds = [];

    /**
     * @var mixed
     */
    protected $eventDispatcher;

    /**
     * @var mixed
     */
    protected $currencyService;

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsShippingEnabled($yesNo)
    {
        $this->isShippingEnabled = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsShippingEnabled()
    {
        return $this->isShippingEnabled;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsTaxEnabled($yesNo)
    {
        $this->isTaxEnabled = $yesNo;
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
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setApplyAutoDiscounts($yesNo)
    {
        $this->applyAutoDiscounts = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getApplyAutoDiscounts()
    {
        return $this->applyAutoDiscounts;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setExcludeDiscountIds($ids = [])
    {
        $this->excludeDiscountIds = $ids;
        return $this;
    }

    /**
     * @return array
     */
    public function getExcludeDiscountIds()
    {
        return $this->excludeDiscountIds;
    }

    /**
     * Assuming that totals are collected based on event priority
     *
     */
    public function collectTotals()
    {
        // collect totals
        $dispatcher = $this->getEventDispatcher();
        $event = new CoreEvent();
        $event->setCart($this->getCart())
            ->setIsShippingEnabled($this->getIsShippingEnabled())
            ->setIsTaxEnabled($this->getIsTaxEnabled())
            ->setCurrencyService($this->getCurrencyService())
            ->setApplyAutoDiscounts($this->getApplyAutoDiscounts())
            ->setExcludeDiscountIds($this->getExcludeDiscountIds());

        $dispatcher->dispatch(CoreEvents::CART_TOTAL, $event);
        $this->setTotals($event->getTotals());
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function getTotal($key)
    {
        return isset($this->totals[$key])
            ? $this->totals[$key]
            : null;
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function setTotals(array $totals)
    {
        $this->totals = $totals;
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
