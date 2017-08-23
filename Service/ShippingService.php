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
     * @var mixed
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
    public function setIsCollectTotalEnabled($isEnabled)
    {
        $this->isCollectTotalEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCollectTotalEnabled()
    {
        return $this->isCollectTotalEnabled;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsMultiShippingEnabled($isEnabled)
    {
        $this->isMultiShippingEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMultiShippingEnabled()
    {
        return $this->isMultiShippingEnabled;
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

        $this->rates = $event->getRates();

        if (!$this->getIsCollectTotalEnabled()
            && $this->rates
        ) {
            foreach($this->rates as $rate) {

                $rate->set('price', '0.00')
                    ->set('base_price', '0.00');

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
        $sourceAddress->fromArray([
            'key' => $key,
            'label' => $label,
            'street' => $street,
            'city' => $city,
            'province' => $province,
            'postcode' => $postcode,
            'country' => $country,
        ]);
        $this->sourceAddresses[$key] = $sourceAddress;
        return $this;
    }

    /**
     * @param $key
     * @return null
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
     * @param array $cartItems
     * @param float|string $addtlPrice for handling costs or flat shipping prices
     * @return RateRequest|null
     */
    public function createRateRequest($srcAddressKey, array $cartItems = [], $addtlPrice = 0.0)
    {
        $request = new RateRequest();
        $sourceAddress = $this->getSourceAddress($srcAddressKey);
        if ($sourceAddress) {

            $request->fromArray([
                'to_array'    => 0,
                'include_all' => 0,
                'postcode' => '',
                'country_id' => '',
                'region' => '',
                'src_postcode' => $sourceAddress->getPostcode(),
                'src_country_id' => $sourceAddress->getCountry(),
                'src_region' => $sourceAddress->getProvince(),
                'source_address_key' => $sourceAddress->getKey(),
                'cart_items' => $cartItems,
                'addtl_price' => $addtlPrice,
            ]);

            return $request;
        }

        $request->fromArray([
            'to_array'    => 0,
            'include_all' => 0,
            'postcode' => '',
            'country_id' => '',
            'region' => '',
            'src_postcode' => '',
            'src_country_id' => '',
            'src_region' => '',
            'source_address_key' => 'main',
            'cart_items' => $cartItems,
            'addtl_price' => $addtlPrice,
        ]);

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
        return $this->getEntityService()
            ->find(EntityConstants::SHIPPING_METHOD, $id);
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
