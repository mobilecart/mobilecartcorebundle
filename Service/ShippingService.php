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

use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\Shipping\FilterShippingRateEvent;
use MobileCart\CoreBundle\Shipping\RateRequest;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\ShippingMethod;

class ShippingService
{
    /**
     * @var bool
     */
    protected $isShippingEnabled = true;

    /**
     * @var bool
     */
    protected $is_multi_shipping_enabled = false;

    /**
     * @var mixed
     */
    protected $eventDispatcher;

    /**
     * @var
     */
    protected $entityService;

    public function __construct()
    {

    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsShippingEnabled($yesNo)
    {
        $isEnabled = ($yesNo != '0' && $yesNo != 'false');
        $this->isShippingEnabled = $isEnabled;
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
     * @param $isEnabled
     * @return $this
     */
    public function setIsMultiShippingEnabled($isEnabled)
    {
        $this->is_multi_shipping_enabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMultiShippingEnabled()
    {
        return $this->is_multi_shipping_enabled;
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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param RateRequest $rateRequest
     * @return mixed
     */
    public function collectShippingRates(RateRequest $rateRequest)
    {
        $event = new FilterShippingRateEvent();
        $event->setRateRequest($rateRequest);
        $this->getEventDispatcher()
            ->dispatch(CoreEvents::SHIPPING_RATE_COLLECT, $event);

        return $event->getRates();
    }

    /**
     * Load a ShippingMethod from a Rate
     * this is mostly used in the admin
     *
     * @param RateRequest $rateRequest
     * @param $code
     * @return bool|ShippingMethod
     */
    public function getShippingMethod(RateRequest $rateRequest, $code)
    {
        $rates = $this->collectShippingRates($rateRequest);

        $rate = isset($rates[$code])
            ? $rates[$code]
            : '';

        if (!$rate) {
            return false;
        }

        $id = $rate->getId()
                ? $rate->getId()
                : $rate->getCode();

        $method = new ShippingMethod();
        $method->setId($id);
        $method->setTitle($rate->getTitle());
        $method->setCompany($rate->getCompany());
        $method->setMethod($rate->getMethod());
        $method->setMinDays($rate->getMinDays());
        $method->setMaxDays($rate->getMaxDays());
        $method->setIsTaxable($rate->getIsTaxable());
        $method->setIsDiscountable($rate->getIsDiscountable());
        $method->setIsPriceDynamic($rate->getIsPriceDynamic());
        $method->setPrice($rate->getPrice());

        return $method;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->getEntityService()
            ->find(EntityConstants::SHIPPING_METHOD, $id);
    }

    /**
     * @param $company
     * @param $method
     */
    public function findByCompanyMethod($company, $method)
    {
        return $this->getEntityService()
            ->findBy(EntityConstants::SHIPPING_METHOD, [
                'company' => $company,
                'method'  => $method,
            ]);
    }
}
