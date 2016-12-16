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

class Customer extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
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
            'id'                  => '',
            'group'               => '',
            'email'               => '',
            'first_name'          => '',
            'last_name'           => '',
            'billing_name'        => '',
            'billing_phone'       => '',
            'billing_street'      => '',
            'billing_city'        => '',
            'billing_region'      => '',
            'billing_postcode'    => '',
            'billing_country_id'  => '',
            'is_shipping_same'    => '',
            'shipping_name'       => '',
            'shipping_phone'      => '',
            'shipping_street'     => '',
            'shipping_city'       => '',
            'shipping_region'     => '',
            'shipping_postcode'   => '',
            'shipping_country_id' => '',
        ];
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
            case 'group':
                $condition->setSourceValue($this->getGroup());
                break;
            case 'billing_state':
                $condition->setSourceValue($this->getBillingState());
                break;
            case 'shipping_state':
                $condition->setSourceValue($this->getShippingState());
                break;
            default:
                //no-op
                break;
        }

        return $condition->isValid();
    }

    /**
     * @return $this
     */
    public function copyBillingToShipping()
    {
        $this->setIsShippingSame(1);
        $this->setShippingName($this->getBillingName());
        $this->setShippingPhone($this->getBillingPhone());
        $this->setShippingCity($this->getBillingCity());
        $this->setShippingCountryId($this->getBillingCountryId());
        $this->setShippingPostcode($this->getBillingPostcode());
        $this->setShippingRegion($this->getBillingRegion());
        $this->setShippingStreet($this->getBillingStreet());
        return $this;
    }

    /**
     * @param $address
     * @return $this
     */
    public function addAddress($address)
    {
        if (!isset($this->data['addresses'])) {
            $this->data['addresses'] = [];
        }
        $this->data['addresses'][] = $address;
        return $this;
    }

    /**
     * @return array
     */
    public function getAddresses()
    {
        if (!isset($this->data['addresses'])) {
            $this->data['addresses'] = [];
        }
        return $this->data['addresses'];
    }
}
