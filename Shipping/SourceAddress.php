<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

/**
 * Class SourceAddress
 * @package MobileCart\CoreBundle\Shipping
 */
class SourceAddress extends ArrayWrapper
{
    const KEY = 'key';
    const LABEL = 'label';
    const STREET = 'street';
    const CITY = 'city';
    const PROVINCE = 'province';
    const POSTCODE = 'postcode';
    const COUNTRY = 'country';

    public function __construct()
    {
        parent::__construct($this->getDefaults());
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            self::KEY      => '',
            self::LABEL    => '',
            self::STREET   => '',
            self::CITY     => '',
            self::PROVINCE => '',
            self::POSTCODE => '',
            self::COUNTRY  => '',
        ];
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        if ($data) {
            foreach($data as $key => $value) {
                switch($key) {
                    case self::KEY:
                        $this->setKey($value);
                        break;
                    case self::LABEL:
                        $this->setLabel($value);
                        break;
                    case self::STREET:
                        $this->setStreet($value);
                        break;
                    case self::CITY:
                        $this->setCity($value);
                        break;
                    case self::PROVINCE:
                        $this->setProvince($value);
                        break;
                    case self::POSTCODE:
                        $this->setPostcode($value);
                        break;
                    case self::COUNTRY:
                        $this->setCountry($value);
                        break;
                    default:

                        break;
                }
            }
        }
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->data[self::KEY] = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->data[self::KEY];
    }

    /**
     * @param $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->data[self::LABEL] = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->data[self::LABEL];
    }

    /**
     * @param $street
     * @return $this
     */
    public function setStreet($street)
    {
        $this->data[self::STREET] = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->data[self::STREET];
    }

    /**
     * @param $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->data[self::CITY] = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->data[self::CITY];
    }

    /**
     * @param $province
     * @return $this
     */
    public function setProvince($province)
    {
        $this->data[self::PROVINCE] = $province;
        return $this;
    }

    /**
     * @return string
     */
    public function getProvince()
    {
        return $this->data[self::PROVINCE];
    }

    /**
     * @param $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        $this->data[self::POSTCODE] = $postcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->data[self::POSTCODE];
    }

    /**
     * @param $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->data[self::COUNTRY] = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->data[self::COUNTRY];
    }
}
