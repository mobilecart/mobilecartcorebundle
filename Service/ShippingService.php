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
use MobileCart\CoreBundle\Shipping\SourceAddress;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\ShippingMethod;
use MobileCart\CoreBundle\CartComponent\Item;

/**
 * Class ShippingService
 * @package MobileCart\CoreBundle\Service
 */
class ShippingService
{
    /**
     * @var bool
     */
    protected $isShippingEnabled = true;

    /**
     * @var bool
     */
    protected $isCollectTotalEnabled = true;

    /**
     * @var bool
     */
    protected $isMultiShippingEnabled = false;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var array
     */
    protected $sourceAddresses = []; // r[key] = ArrayWrapper

    /**
     * @var array
     */
    protected $rates = [];

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsShippingEnabled($isEnabled)
    {
        $this->isShippingEnabled = (bool) $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsShippingEnabled()
    {
        return (bool) $this->isShippingEnabled;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsCollectTotalEnabled($isEnabled)
    {
        $this->isCollectTotalEnabled = (bool) $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCollectTotalEnabled()
    {
        return (bool) $this->isCollectTotalEnabled;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsMultiShippingEnabled($isEnabled)
    {
        $this->isMultiShippingEnabled = (bool) $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMultiShippingEnabled()
    {
        return (bool) $this->isMultiShippingEnabled;
    }

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
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
     * @return array|\MobileCart\CoreBundle\Shipping\Rate[]
     */
    public function collectShippingRates(RateRequest $rateRequest)
    {
        $event = new FilterShippingRateEvent();
        $event->setRateRequest($rateRequest);
        $this->getEventDispatcher()
            ->dispatch(CoreEvents::SHIPPING_RATE_COLLECT, $event);

        $this->rates = $event->getRates();

        if (!$this->getIsCollectTotalEnabled()
            && $this->rates
        ) {
            foreach($this->rates as $rate) {

                $rate->setPrice(0.00)
                    ->setBasePrice(0.00);
            }
        }

        return $this->rates;
    }

    /**
     * @return array
     */
    public function getRates()
    {
        return $this->rates;
    }

    /**
     * @param SourceAddress $sourceAddress
     * @return $this
     */
    public function addSourceAddress(SourceAddress $sourceAddress)
    {
        $this->sourceAddresses[$sourceAddress->getKey()] = $sourceAddress;
        return $this;
    }

    /**
     * Setter method
     *
     * @param $key
     * @param $label
     * @param $street
     * @param $city
     * @param $province
     * @param $postcode
     * @param $country
     * @return $this
     */
    public function setSourceAddress($key, $label, $street, $city, $province, $postcode, $country)
    {
        $sourceAddress = new SourceAddress();
        $sourceAddress->setKey($key)
            ->setLabel($label)
            ->setStreet($street)
            ->setCity($city)
            ->setProvince($province)
            ->setPostcode($postcode)
            ->setCountry($country);

        $this->sourceAddresses[$key] = $sourceAddress;
        return $this;
    }

    /**
     * @param $key
     * @return SourceAddress|null
     */
    public function getSourceAddress($key)
    {
        return isset($this->sourceAddresses[$key])
            ? $this->sourceAddresses[$key]
            : null;
    }

    /**
     * @return array
     */
    public function getSourceAddresses()
    {
        return $this->sourceAddresses;
    }

    /**
     * @param $srcAddressKey
     * @param array|Item[] $cartItems
     * @param float|string $addtlPrice for handling costs or flat shipping prices
     * @return RateRequest
     */
    public function createRateRequest($srcAddressKey, array $cartItems = [], $addtlPrice = 0.0)
    {
        $skus = [];
        $productIds = [];
        if ($cartItems) {
            foreach($cartItems as $cartItem) {
                $skus[] = $cartItem->getSku();
                $productIds[] = $cartItem->getProductId();
            }
        }


        $sourceAddress = $this->getSourceAddress($srcAddressKey);
        if ($sourceAddress) {
            // destination info is set later

            $request = new RateRequest();
            $request->setDestRegion('')
                ->setDestPostcode('')
                ->setDestCountryId('')
                ->setSrcRegion($sourceAddress->getProvince())
                ->setSrcPostcode($sourceAddress->getPostcode())
                ->setSrcCountryId($sourceAddress->getCountry())
                ->setSourceAddressKey($sourceAddress->getKey())
                ->setAddtlPrice($addtlPrice)
                ->setCartItems($cartItems)
                ->setProductIds($productIds)
                ->setSkus($skus);

            return $request;
        }

        $request = new RateRequest();
        $request->setDestRegion('')
            ->setDestPostcode('')
            ->setDestCountryId('')
            ->setSrcPostcode('')
            ->setSrcCountryId('')
            ->setSrcRegion('')
            ->setSourceAddressKey('main')
            ->setAddtlPrice($addtlPrice)
            ->setCartItems($cartItems)
            ->setProductIds($productIds)
            ->setSkus($skus);

        return $request;
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

        /** @var \MobileCart\CoreBundle\Shipping\Rate $rate */
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
     * Locate a flat rate in the database
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->getEntityService()->find(EntityConstants::SHIPPING_METHOD, $id);
    }

    /**
     * Locate a flat rate in the database
     *
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
