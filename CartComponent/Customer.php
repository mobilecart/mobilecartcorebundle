<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\CartComponent;

/**
 * Class Customer
 * @package MobileCart\CoreBundle\CartComponent
 */
class Customer extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    const ID = 'id';
    const EMAIL = 'email';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const BILLING_NAME = 'billing_name';
    const BILLING_STREET = 'billing_street';
    const BILLING_STREET2 = 'billing_street2';
    const BILLING_CITY = 'billing_city';
    const BILLING_REGION = 'billing_region';
    const BILLING_POSTCODE = 'billing_postcode';
    const BILLING_COUNTRY_ID = 'billing_country_id';
    const BILLING_PHONE = 'billing_phone';
    const IS_SHIPPING_SAME = 'is_shipping_same';
    const SHIPPING_NAME = 'shipping_name';
    const SHIPPING_STREET = 'shipping_street';
    const SHIPPING_STREET2 = 'shipping_street2';
    const SHIPPING_CITY = 'shipping_city';
    const SHIPPING_REGION = 'shipping_region';
    const SHIPPING_POSTCODE = 'shipping_postcode';
    const SHIPPING_COUNTRY_ID = 'shipping_country_id';
    const SHIPPING_PHONE = 'shipping_phone';
    const GROUP = 'group';
    const GROUPS = 'groups';
    const ADDRESSES = 'addresses';

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
            self::ID                  => 0,
            self::EMAIL               => '',
            self::FIRST_NAME          => '',
            self::LAST_NAME           => '',
            self::BILLING_NAME        => '',
            self::BILLING_PHONE       => '',
            self::BILLING_STREET      => '',
            self::BILLING_STREET2      => '',
            self::BILLING_CITY        => '',
            self::BILLING_REGION      => '',
            self::BILLING_POSTCODE    => '',
            self::BILLING_COUNTRY_ID  => '',
            self::IS_SHIPPING_SAME    => false,
            self::SHIPPING_NAME       => '',
            self::SHIPPING_PHONE      => '',
            self::SHIPPING_STREET     => '',
            self::SHIPPING_STREET2     => '',
            self::SHIPPING_CITY       => '',
            self::SHIPPING_REGION     => '',
            self::SHIPPING_POSTCODE   => '',
            self::SHIPPING_COUNTRY_ID => '',
            self::GROUPS              => [],
            self::ADDRESSES           => [],
        ];
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->data[self::ID] = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->data[self::ID];
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->data[self::EMAIL] = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->data[self::EMAIL];
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->data[self::FIRST_NAME] = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->data[self::FIRST_NAME];
    }

    /**
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->data[self::LAST_NAME] = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->data[self::LAST_NAME];
    }

    /**
     * @param string $billingName
     * @return $this
     */
    public function setBillingName($billingName)
    {
        $this->data[self::BILLING_NAME] = $billingName;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingName()
    {
        return $this->data[self::BILLING_NAME];
    }

    /**
     * @param string $billingPhone
     * @return $this
     */
    public function setBillingPhone($billingPhone)
    {
        $this->data[self::BILLING_PHONE] = $billingPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingPhone()
    {
        return $this->data[self::BILLING_PHONE];
    }

    /**
     * @param string $billingStreet
     * @return $this
     */
    public function setBillingStreet($billingStreet)
    {
        $this->data[self::BILLING_STREET] = $billingStreet;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingStreet()
    {
        return $this->data[self::BILLING_STREET];
    }

    /**
     * @param string $billingStreet2
     * @return $this
     */
    public function setBillingStreet2($billingStreet2)
    {
        $this->data[self::BILLING_STREET2] = $billingStreet2;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingStreet2()
    {
        return $this->data[self::BILLING_STREET2];
    }

    /**
     * @param string $billingCity
     * @return $this
     */
    public function setBillingCity($billingCity)
    {
        $this->data[self::BILLING_CITY] = $billingCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingCity()
    {
        return $this->data[self::BILLING_CITY];
    }

    /**
     * @param string $billingRegion
     * @return $this
     */
    public function setBillingRegion($billingRegion)
    {
        $this->data[self::BILLING_REGION] = $billingRegion;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingRegion()
    {
        return $this->data[self::BILLING_REGION];
    }

    /**
     * @param string $billingPostcode
     * @return $this
     */
    public function setBillingPostcode($billingPostcode)
    {
        $this->data[self::BILLING_POSTCODE] = $billingPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingPostcode()
    {
        return $this->data[self::BILLING_POSTCODE];
    }

    /**
     * @param string $billingCountryId
     * @return $this
     */
    public function setBillingCountryId($billingCountryId)
    {
        $this->data[self::BILLING_COUNTRY_ID] = $billingCountryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingCountryId()
    {
        return $this->data[self::BILLING_COUNTRY_ID];
    }

    /**
     * @param bool $isShippingSame
     * @return $this
     */
    public function setIsShippingSame($isShippingSame)
    {
        $this->data[self::IS_SHIPPING_SAME] = (bool) $isShippingSame;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsShippingSame()
    {
        return (bool) $this->data[self::IS_SHIPPING_SAME];
    }

    /**
     * @param string $shippingName
     * @return $this
     */
    public function setShippingName($shippingName)
    {
        $this->data[self::SHIPPING_NAME] = $shippingName;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingName()
    {
        return $this->data[self::SHIPPING_NAME];
    }

    /**
     * @param string $shippingPhone
     * @return $this
     */
    public function setShippingPhone($shippingPhone)
    {
        $this->data[self::SHIPPING_PHONE] = $shippingPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingPhone()
    {
        return $this->data[self::SHIPPING_PHONE];
    }

    /**
     * @param string $shippingStreet
     * @return $this
     */
    public function setShippingStreet($shippingStreet)
    {
        $this->data[self::SHIPPING_STREET] = $shippingStreet;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingStreet()
    {
        return $this->data[self::SHIPPING_STREET];
    }

    /**
     * @param string $shippingStreet2
     * @return $this
     */
    public function setShippingStreet2($shippingStreet2)
    {
        $this->data[self::SHIPPING_STREET2] = $shippingStreet2;
        return $this;
    }

    /**
     * @param string $shippingCity
     * @return $this
     */
    public function setShippingCity($shippingCity)
    {
        $this->data[self::SHIPPING_CITY] = $shippingCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingCity()
    {
        return $this->data[self::SHIPPING_CITY];
    }

    /**
     * @param string $shippingRegion
     * @return $this
     */
    public function setShippingRegion($shippingRegion)
    {
        $this->data[self::SHIPPING_REGION] = $shippingRegion;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingRegion()
    {
        return $this->data[self::SHIPPING_REGION];
    }

    /**
     * @param string $shippingPostcode
     * @return $this
     */
    public function setShippingPostcode($shippingPostcode)
    {
        $this->data[self::SHIPPING_POSTCODE] = $shippingPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingPostcode()
    {
        return $this->data[self::SHIPPING_POSTCODE];
    }

    /**
     * @param string $shippingCountryId
     * @return $this
     */
    public function setShippingCountryId($shippingCountryId)
    {
        $this->data[self::SHIPPING_COUNTRY_ID] = $shippingCountryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingCountryId()
    {
        return $this->data[self::SHIPPING_COUNTRY_ID];
    }

    /**
     * @param $group
     * @return $this
     */
    public function addGroup($group)
    {
        $this->data[self::GROUPS][] = $group;
        return $this;
    }

    /**
     * Set array of Group names
     *
     * @param array $groups
     * @return $this
     */
    public function setGroups(array $groups)
    {
        $this->data[self::GROUPS] = $groups;
        return $this;
    }

    /**
     * Return array of Group names
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->data[self::GROUPS];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Validate a discount condition against customer properties
     *
     * @param RuleCondition
     * @return bool
     */
    public function isValidCondition(RuleCondition $condition)
    {
        switch($condition->getSourceField()) {
            case self::GROUP:
                $condition->setSourceValue($this->getGroups()); // todo : make sure this works
                break;
            default:
                $condition->setSourceValue($this->get($condition->getSourceField()));
                break;
        }

        return $condition->isValid();
    }

    /**
     * Check whether this shipment validates a hierarchy of discount conditions
     *
     * @param RuleConditionCompare
     * @return bool
     */
    public function isValidConditionCompare(RuleConditionCompare $compare)
    {
        return $compare->isValid($this);
    }

    /**
     * @return $this
     */
    public function copyBillingToShipping()
    {
        $this->setIsShippingSame(true);
        $this->setShippingName($this->getBillingName());
        $this->setShippingStreet($this->getBillingStreet());
        $this->setShippingStreet2($this->getBillingStreet2());
        $this->setShippingCity($this->getBillingCity());
        $this->setShippingRegion($this->getBillingRegion());
        $this->setShippingCountryId($this->getBillingCountryId());
        $this->setShippingPostcode($this->getBillingPostcode());
        $this->setShippingPhone($this->getBillingPhone());
        return $this;
    }

    /**
     * @param $address
     * @return $this
     */
    public function addAddress($address)
    {
        $this->data[self::ADDRESSES][] = $address;
        return $this;
    }

    /**
     * @return array
     */
    public function getAddresses()
    {
        return $this->data[self::ADDRESSES];
    }

    /**
     * @param array $addresses
     * @return $this
     */
    public function setAddresses(array $addresses)
    {
        $this->data[self::ADDRESSES] = $addresses;
        return $this;
    }
}
