<?php

namespace MobileCart\CoreBundle\CartComponent;

/**
 * Class CustomerAddress
 * @package MobileCart\CoreBundle\CartComponent
 */
class CustomerAddress extends ArrayWrapper
{
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const COMPANY = 'company';
    const STREET = 'street';
    const STREET2 = 'street2';
    const CITY = 'city';
    const REGION = 'region';
    const POSTCODE = 'postcode';
    const COUNTRY_ID = 'country_id';
    const PHONE = 'phone';

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
            self::ID          => 0,
            self::CUSTOMER_ID => 0,
            self::FIRSTNAME   => '',
            self::LASTNAME    => '',
            self::COMPANY     => '',
            self::STREET      => '',
            self::STREET2     => '',
            self::CITY        => '',
            self::REGION      => '',
            self::POSTCODE    => '',
            self::COUNTRY_ID  => '',
            self::PHONE       => '',
        ];
    }

    public function fromArray(array $data)
    {
        if ($data) {
            foreach($data as $key => $value) {
                switch($key) {
                    case self::ID:
                        $this->setId($value);
                        break;
                    case self::CUSTOMER_ID:
                        $this->setCustomerId($value);
                        break;
                    case self::FIRSTNAME:
                        $this->setFirstname($value);
                        break;
                    case self::LASTNAME:
                        $this->setLastname($value);
                        break;
                    case self::COMPANY:
                        $this->setCompany($value);
                        break;
                    case self::STREET:
                        $this->setStreet($value);
                        break;
                    case self::STREET2:
                        $this->setStreet2($value);
                        break;
                    case self::CITY:
                        $this->setCity($value);
                        break;
                    case self::REGION:
                        $this->setRegion($value);
                        break;
                    case self::POSTCODE:
                        $this->setPostcode($value);
                        break;
                    case self::COUNTRY_ID:
                        $this->setCountryId($value);
                        break;
                    case self::PHONE:
                        $this->setPhone($value);
                        break;
                    default:
                        if ($value instanceof \stdClass || is_object($value)) {
                            $this->data[$key] = new ArrayWrapper(get_object_vars($value));
                        } elseif (is_array($value)) {
                            $this->data[$key] = $value;
                        } elseif (is_scalar($value)) {
                            $this->data[$key] = $value;
                        }
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return "{$this->getStreet()} {$this->getStreet2()} {$this->getCity()}, {$this->getRegion()}";
    }

    /**
     * @param int|string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->data[self::ID] = $id;
        return $this;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->data[self::ID];
    }

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->data[self::CUSTOMER_ID] = (int) $customerId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return (int) $this->data[self::CUSTOMER_ID];
    }

    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->data[self::FIRSTNAME] = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->data[self::FIRSTNAME];
    }

    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->data[self::LASTNAME] = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->data[self::LASTNAME];
    }

    /**
     * @param $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->data[self::COMPANY] = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->data[self::COMPANY];
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
     * @param $street2
     * @return $this
     */
    public function setStreet2($street2)
    {
        $this->data[self::STREET2] = $street2;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet2()
    {
        return $this->data[self::STREET2];
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
     * @param $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->data[self::REGION] = $region;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->data[self::REGION];
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
     * @param $countryId
     * @return $this
     */
    public function setCountryId($countryId)
    {
        $this->data[self::COUNTRY_ID] = $countryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryId()
    {
        return $this->data[self::COUNTRY_ID];
    }

    /**
     * @param $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->data[self::PHONE] = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->data[self::PHONE];
    }
}
