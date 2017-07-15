<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;


/**
 * MobileCart\CoreBundle\Entity\Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CustomerRepository")
 */
class Customer
    implements AdvancedUserInterface, CartEntityEAVInterface, \Serializable
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var string $default_locale
     *
     * @ORM\Column(name="default_locale", type="string", length=4, nullable=true)
     */
    private $default_locale;

    /**
     * @var string $default_currency
     *
     * @ORM\Column(name="default_currency", type="string", length=4, nullable=true)
     */
    private $default_currency;

    /**
     * @var string $first_name
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $first_name;

    /**
     * @var string $last_name
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $last_name;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="text", nullable=true)
     */
    private $hash;

    /**
     * @var string $confirm_hash
     *
     * @ORM\Column(name="confirm_hash", type="text", nullable=true)
     */
    private $confirm_hash;

    /**
     * @var string $billing_name
     *
     * @ORM\Column(name="billing_name", type="string", length=255, nullable=true)
     */
    private $billing_name;

    /**
     * @var string $billing_company
     *
     * @ORM\Column(name="billing_company", type="string", length=255, nullable=true)
     */
    private $billing_company;

    /**
     * @var string $billing_phone
     *
     * @ORM\Column(name="billing_phone", type="string", length=24, nullable=true)
     */
    private $billing_phone;

    /**
     * @var string $billing_street
     *
     * @ORM\Column(name="billing_street", type="string", length=255, nullable=true)
     */
    private $billing_street;

    /**
     * @var string $billing_street2
     *
     * @ORM\Column(name="billing_street2", type="string", length=255, nullable=true)
     */
    private $billing_street2;

    /**
     * @var string $billing_city
     *
     * @ORM\Column(name="billing_city", type="string", length=255, nullable=true)
     */
    private $billing_city;

    /**
     * @var string $billing_region
     *
     * @ORM\Column(name="billing_region", type="string", length=255, nullable=true)
     */
    private $billing_region;

    /**
     * @var string $billing_postcode
     *
     * @ORM\Column(name="billing_postcode", type="string", length=16, nullable=true)
     */
    private $billing_postcode;

    /**
     * @var string $billing_country_id
     *
     * @ORM\Column(name="billing_country_id", type="string", length=2, nullable=true)
     */
    private $billing_country_id;

    /**
     * @var boolean $is_shipping_same
     *
     * @ORM\Column(name="is_shipping_same", type="boolean", nullable=true)
     */
    private $is_shipping_same;

    /**
     * @var string $shipping_name
     *
     * @ORM\Column(name="shipping_name", type="string", length=255, nullable=true)
     */
    private $shipping_name;

    /**
     * @var string $shipping_company
     *
     * @ORM\Column(name="shipping_company", type="string", length=255, nullable=true)
     */
    private $shipping_company;

    /**
     * @var string $shipping_phone
     *
     * @ORM\Column(name="shipping_phone", type="string", length=24, nullable=true)
     */
    private $shipping_phone;

    /**
     * @var string $shipping_street
     *
     * @ORM\Column(name="shipping_street", type="string", length=255, nullable=true)
     */
    private $shipping_street;

    /**
     * @var string $shipping_street2
     *
     * @ORM\Column(name="shipping_street2", type="string", length=255, nullable=true)
     */
    private $shipping_street2;

    /**
     * @var string $shipping_city
     *
     * @ORM\Column(name="shipping_city", type="string", length=255, nullable=true)
     */
    private $shipping_city;

    /**
     * @var string $shipping_region
     *
     * @ORM\Column(name="shipping_region", type="string", length=255, nullable=true)
     */
    private $shipping_region;

    /**
     * @var string $shipping_postcode
     *
     * @ORM\Column(name="shipping_postcode", type="string", length=16, nullable=true)
     */
    private $shipping_postcode;

    /**
     * @var string $shipping_country_id
     *
     * @ORM\Column(name="shipping_country_id", type="string", length=2, nullable=true)
     */
    private $shipping_country_id;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerToken
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerToken", mappedBy="customer")
     */
    private $tokens;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerAddress
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerAddress", mappedBy="customer")
     */
    private $addresses;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerGroup
     *
     * @ORM\ManyToMany(targetEntity="CustomerGroup", mappedBy="customers")
     */
    private $groups;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSet
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_set_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $item_var_set;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerVarValueDatetime
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerVarValueDatetime", mappedBy="parent")
     */
    private $var_values_datetime;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerVarValueDecimal
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerVarValueDecimal", mappedBy="parent")
     */
    private $var_values_decimal;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerVarValueInt
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerVarValueInt", mappedBy="parent")
     */
    private $var_values_int;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerVarValueText
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerVarValueText", mappedBy="parent")
     */
    private $var_values_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerVarValueVarchar
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerVarValueVarchar", mappedBy="parent")
     */
    private $var_values_varchar;

    /**
     * @var int $failed_logins
     *
     * @ORM\Column(name="failed_logins", type="integer", nullable=true)
     */
    private $failed_logins;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="locked_at", type="datetime", nullable=true)
     */
    private $locked_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true)
     */
    private $last_login_at;

    /**
     * @var string $api_key
     *
     * @ORM\Column(name="api_key", type="string", length=255, nullable=true)
     */
    private $api_key;

    /**
     * @var boolean $is_enabled
     *
     * @ORM\Column(name="is_enabled", type="boolean", nullable=true)
     */
    private $is_enabled;

    /**
     * @var boolean $is_expired
     *
     * @ORM\Column(name="is_expired", type="boolean", nullable=true)
     */
    private $is_expired;

    /**
     * @var boolean $is_locked
     *
     * @ORM\Column(name="is_locked", type="boolean", nullable=true)
     */
    private $is_locked;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="password_updated_at", type="datetime", nullable=true)
     */
    private $password_updated_at;

    /**
     * @var boolean $is_password_expired
     *
     * @ORM\Column(name="is_password_expired", type="boolean", nullable=true)
     */
    private $is_password_expired;

    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tokens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_datetime = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_decimal = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_int = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_text = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_varchar = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getEmail(); // email is required. first/last name are not
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CUSTOMER;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $vars = get_object_vars($this);
        if (array_key_exists($key, $vars)) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function fromArray($data)
    {
        if (!$data) {
            return $this;
        }

        foreach($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Lazy-loading getter
     *  ideal for usage in the View layer
     *
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        $data = $this->getBaseData();
        if (isset($data[$key])) {
            return $data[$key];
        }

        $data = $this->getData();
        if (isset($data[$key])) {

            if (is_array($data[$key])) {
                return implode(',', $data[$key]);
            }

            return $data[$key];
        }

        return '';
    }

    /**
     * Get All Data or specific key of data
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        if (strlen($key) > 0) {

            $data = $this->getBaseData();
            if (isset($data[$key])) {
                return $data[$key];
            }

            switch($key) {
                case 'customer_group':

                    break;
                default:
                    // no-op
                    break;
            }

            $data = $this->getVarValuesData();
            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return array_merge($this->getVarValuesData(), $this->getBaseData());
    }

    /**
     * @return array
     */
    public function getLuceneVarValuesData()
    {
        // Note:
        // be careful with adding foreign relationships here
        // since it will add 1 query every time an item is loaded

        $pData = $this->getBaseData();

        $varValues = $this->getVarValues();
        if (!$varValues->count()) {
            return $pData;
        }

        foreach($varValues as $itemVarValue) {

            /** @var ItemVar $itemVar */
            $itemVar = $itemVarValue->getItemVar();

            $value = $itemVarValue->getValue();
            switch($itemVar->getDatatype()) {
                case 'int':
                    $value = (int) $value;
                    break;
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'datetime':
                    // for Lucene
                    $value = gmdate('Y-m-d\TH:i:s\Z', strtotime($value));
                    break;
                default:
                    $value = (string) $value;
                    break;
            }

            if ($itemVar->getFormInput() == 'multiselect') {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = array();
                }
                $data[$itemVar->getCode()][] = $value;
            } else {
                $data[$itemVar->getCode()] = $value;
            }

        }

        return array_merge($this->getVarValuesData(), $pData);
    }

    /**
     * Get Var Values as associative Array
     *
     * @return array
     */
    public function getVarValuesData()
    {
        $varSet = $this->getItemVarSet();
        $varSetId = ($varSet instanceof ItemVarSet)
            ? $varSet->getId()
            : null;

        $data = $this->getBaseData();
        $data['var_set_id'] = $varSetId;
        //$data['tags'] = $this->getTagsData();

        $varValues = $this->getVarValues();
        if (!$varValues) {
            return $data;
        }

        foreach($varValues as $itemVarValue) {

            /** @var ItemVar $itemVar */
            $itemVar = $itemVarValue->getItemVar();

            $value = $itemVarValue->getValue();
            switch($itemVar->getDatatype()) {
                case 'int':
                    $value = (int) $value;
                    break;
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'datetime':
                    $value = gmdate('Y-m-d H:i:s', strtotime($value));
                    break;
                default:
                    $value = (string) $value;
                    break;
            }

            if ($itemVar->getFormInput() == 'multiselect') {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = [];
                }
                $data[$itemVar->getCode()][] = $value;
            } else {
                $data[$itemVar->getCode()] = $value;
            }

        }

        return $data;
    }

    /**
     *
     * @return array
     */
    public function getVarValues()
    {
        $values = new ArrayCollection();
        $datetimes = $this->getVarValuesDatetime();
        $decimals = $this->getVarValuesDecimal();
        $ints = $this->getVarValuesInt();
        $texts = $this->getVarValuesText();
        $varchars = $this->getVarValuesVarchar();

        if ($datetimes) {
            foreach($datetimes as $value) {
                $values->add($value);
            }
        }

        if ($decimals) {
            foreach($decimals as $value) {
                $values->add($value);
            }
        }

        if ($ints) {
            foreach($ints as $value) {
                $values->add($value);
            }
        }

        if ($texts) {
            foreach($texts as $value) {
                $values->add($value);
            }
        }

        if ($varchars) {
            foreach($varchars as $value) {
                $values->add($value);
            }
        }

        return $values;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->getBaseData());
    }

    /**
     * Unserialize for Symfony session
     *
     * @param string $str
     * @return $this|void
     */
    public function unserialize($str)
    {
        $baseData = $this->getBaseData();
        $data = unserialize($str);
        if (is_array($data) && $data) {
            foreach($data as $k => $v) {
                if (array_key_exists($k, $baseData)) {
                    $this->set($k, $v);
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        // security concerns:
        //  prevent the hash from being communicated in api responses

        return [
            'id'                  => $this->getId(),
            'default_locale'      => $this->getDefaultLocale(),
            'default_currency'    => $this->getDefaultCurrency(),
            // 'customer_groups'               => '', todo
            'email'               => $this->getEmail(),
            // 'hash'                => $this->getHash(),
            // 'confirm_hash'        => $this->getConfirmHash(),
            'name'                => $this->getName(),
            'first_name'          => $this->getFirstName(),
            'last_name'           => $this->getLastName(),
            'billing_name'        => $this->getBillingName(),
            'billing_company'     => $this->getBillingCompany(),
            'billing_phone'       => $this->getBillingPhone(),
            'billing_street'      => $this->getBillingStreet(),
            'billing_street2'     => $this->getBillingStreet2(),
            'billing_city'        => $this->getBillingCity(),
            'billing_region'      => $this->getBillingRegion(),
            'billing_postcode'    => $this->getBillingPostcode(),
            'billing_country_id'  => $this->getBillingCountryId(),
            'is_shipping_same'    => $this->getIsShippingSame(),
            'shipping_name'       => $this->getShippingName(),
            'shipping_company'    => $this->getShippingCompany(),
            'shipping_phone'      => $this->getShippingPhone(),
            'shipping_street'     => $this->getShippingStreet(),
            'shipping_street2'    => $this->getShippingStreet2(),
            'shipping_city'       => $this->getShippingCity(),
            'shipping_region'     => $this->getShippingRegion(),
            'shipping_postcode'   => $this->getShippingPostcode(),
            'shipping_country_id' => $this->getShippingCountryId(),
            'failed_logins'       => $this->getFailedLogins(),
            'locked_at'           => $this->getLockedAt(),
            'last_login_at'       => $this->getLastLoginAt(),
            'api_key'             => $this->getApiKey(), // needed for authenticated REST communication
            'is_enabled'          => $this->getIsEnabled(),
            'is_expired'          => $this->getIsExpired(),
            'is_locked'           => $this->getIsLocked(),
            'password_updated_at' => $this->getPasswordUpdatedAt(),
            'is_password_expired' => $this->getIsPasswordExpired(),
        ];
    }

    /**
     * Symfony Security
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Symfony Security
     *
     * @return null|string
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Symfony Security
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->hash;
    }

    /**
     * Symfony Security
     *
     * @return $this
     */
    public function eraseCredentials()
    {
        //$this->hash = '';
        return $this;
    }

    /**
     * @return array|\Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * @return bool|void
     */
    public function isAccountNonExpired()
    {
        return !$this->getIsExpired();
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked()
    {
        return !$this->getIsLocked();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getIsEnabled();
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired()
    {
        return !$this->getIsPasswordExpired();
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param $defaultLocale
     * @return $this
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->default_locale = $defaultLocale;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->default_locale;
    }

    /**
     * @param $defaultCurrency
     * @return $this
     */
    public function setDefaultCurrency($defaultCurrency)
    {
        $this->default_currency = $defaultCurrency;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultCurrency()
    {
        return $this->default_currency;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setBillingName($name)
    {
        $this->billing_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingName()
    {
        return $this->billing_name;
    }

    /**
     * @param $billingCompany
     * @return $this
     */
    public function setBillingCompany($billingCompany)
    {
        $this->billing_company = $billingCompany;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingCompany()
    {
        return $this->billing_company;
    }

    /**
     * @param $billingPhone
     * @return $this
     */
    public function setBillingPhone($billingPhone)
    {
        $this->billing_phone = $billingPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingPhone()
    {
        return $this->billing_phone;
    }

    /**
     * Set billing_street
     *
     * @param string $billingStreet
     * @return $this
     */
    public function setBillingStreet($billingStreet)
    {
        $this->billing_street = $billingStreet;
        return $this;
    }

    /**
     * Get billing_street
     *
     * @return string 
     */
    public function getBillingStreet()
    {
        return $this->billing_street;
    }

    /**
     * Set billing_street2
     *
     * @param string $billingStreet2
     * @return $this
     */
    public function setBillingStreet2($billingStreet2)
    {
        $this->billing_street2 = $billingStreet2;
        return $this;
    }

    /**
     * Get billing_street2
     *
     * @return string
     */
    public function getBillingStreet2()
    {
        return $this->billing_street2;
    }

    /**
     * Set billing_city
     *
     * @param string $billingCity
     * @return $this
     */
    public function setBillingCity($billingCity)
    {
        $this->billing_city = $billingCity;
        return $this;
    }

    /**
     * Get billing_city
     *
     * @return string 
     */
    public function getBillingCity()
    {
        return $this->billing_city;
    }

    /**
     * Set billing_region
     *
     * @param string $billingRegion
     * @return $this
     */
    public function setBillingRegion($billingRegion)
    {
        $this->billing_region = $billingRegion;
        return $this;
    }

    /**
     * Get billing_region
     *
     * @return string 
     */
    public function getBillingRegion()
    {
        return $this->billing_region;
    }

    /**
     * Set billing_postcode
     *
     * @param string $billingPostcode
     * @return $this
     */
    public function setBillingPostcode($billingPostcode)
    {
        $this->billing_postcode = $billingPostcode;
        return $this;
    }

    /**
     * Get billing_postcode
     *
     * @return string 
     */
    public function getBillingPostcode()
    {
        return $this->billing_postcode;
    }

    /**
     * @param $countryId
     * @return $this
     */
    public function setBillingCountryId($countryId)
    {
        $this->billing_country_id = $countryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingCountryId()
    {
        return $this->billing_country_id;
    }

    /**
     * Set is_shipping_same
     *
     * @param boolean $isShippingSame
     * @return $this
     */
    public function setIsShippingSame($isShippingSame)
    {
        $this->is_shipping_same = (bool) $isShippingSame;
        return $this;
    }

    /**
     * Get is_shipping_same
     *
     * @return boolean 
     */
    public function getIsShippingSame()
    {
        return $this->is_shipping_same;
    }

    /**
     * @return $this
     */
    public function copyBillingToShipping()
    {
        $this->setIsShippingSame(1);
        $this->setShippingName($this->getBillingName());
        $this->setShippingCompany($this->getBillingCompany());
        $this->setShippingPhone($this->getBillingPhone());
        $this->setShippingStreet($this->getBillingStreet());
        $this->setShippingCity($this->getBillingCity());
        $this->setShippingRegion($this->getBillingRegion());
        $this->setShippingPostcode($this->getBillingPostcode());
        $this->setShippingCountryId($this->getBillingCountryId());
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setShippingName($name)
    {
        $this->shipping_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingName()
    {
        return $this->shipping_name;
    }

    /**
     * @param $shippingCompany
     * @return $this
     */
    public function setShippingCompany($shippingCompany)
    {
        $this->shipping_company = $shippingCompany;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingCompany()
    {
        return $this->shipping_company;
    }

    /**
     * @param $shippingPhone
     * @return $this
     */
    public function setShippingPhone($shippingPhone)
    {
        $this->shipping_phone = $shippingPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingPhone()
    {
        return $this->shipping_phone;
    }

    /**
     * Set shipping_street
     *
     * @param string $shippingStreet
     * @return $this
     */
    public function setShippingStreet($shippingStreet)
    {
        $this->shipping_street = $shippingStreet;
        return $this;
    }

    /**
     * Get shipping_street
     *
     * @return string 
     */
    public function getShippingStreet()
    {
        return $this->shipping_street;
    }

    /**
     * Set shipping_street2
     *
     * @param string $shippingStreet2
     * @return $this
     */
    public function setShippingStreet2($shippingStreet2)
    {
        $this->shipping_street2 = $shippingStreet2;
        return $this;
    }

    /**
     * Get shipping_street2
     *
     * @return string
     */
    public function getShippingStreet2()
    {
        return $this->shipping_street2;
    }

    /**
     * Set shipping_city
     *
     * @param string $shippingCity
     * @return $this
     */
    public function setShippingCity($shippingCity)
    {
        $this->shipping_city = $shippingCity;
        return $this;
    }

    /**
     * Get shipping_city
     *
     * @return string 
     */
    public function getShippingCity()
    {
        return $this->shipping_city;
    }

    /**
     * Set shipping_region
     *
     * @param string $shippingRegion
     * @return $this
     */
    public function setShippingRegion($shippingRegion)
    {
        $this->shipping_region = $shippingRegion;
        return $this;
    }

    /**
     * Get shipping_region
     *
     * @return string 
     */
    public function getShippingRegion()
    {
        return $this->shipping_region;
    }

    /**
     * Set shipping_postcode
     *
     * @param string $shippingPostcode
     * @return $this
     */
    public function setShippingPostcode($shippingPostcode)
    {
        $this->shipping_postcode = $shippingPostcode;
        return $this;
    }

    /**
     * Get shipping_postcode
     *
     * @return string 
     */
    public function getShippingPostcode()
    {
        return $this->shipping_postcode;
    }

    /**
     * @param $countryId
     * @return $this
     */
    public function setShippingCountryId($countryId)
    {
        $this->shipping_country_id = $countryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingCountryId()
    {
        return $this->shipping_country_id;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set confirm_hash
     *
     * @param string $confirmHash
     * @return $this
     */
    public function setConfirmHash($confirmHash)
    {
        $this->confirm_hash = $confirmHash;
        return $this;
    }

    /**
     * Get confirm_hash
     *
     * @return string
     */
    public function getConfirmHash()
    {
        return $this->confirm_hash;
    }

    /**
     * @param CustomerToken $customerToken
     * @return $this
     */
    public function addToken(CustomerToken $customerToken)
    {
        $this->tokens[] = $customerToken;
        return $this;
    }

    /**
     * Get tokens
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param CustomerAddress $customerAddress
     * @return $this
     */
    public function addAddress(CustomerAddress $customerAddress)
    {
        $this->addresses[] = $customerAddress;
        return $this;
    }

    /**
     * @return ArrayCollection|CustomerAddress
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param CustomerGroup $customerGroup
     * @return $this
     */
    public function addGroup(CustomerGroup $customerGroup)
    {
        $this->groups[] = $customerGroup;
        return $this;
    }

    /**
     * @return ArrayCollection|CustomerGroup
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param ItemVarSet $itemVarSet
     * @return $this
     */
    public function setItemVarSet(ItemVarSet $itemVarSet)
    {
        $this->item_var_set = $itemVarSet;
        return $this;
    }

    /**
     * Get item_var_set
     *
     * @return \MobileCart\CoreBundle\Entity\ItemVarSet
     */
    public function getItemVarSet()
    {
        return $this->item_var_set;
    }

    /**
     * @param CustomerVarValueDecimal $itemVarValues
     * @return $this
     */
    public function addVarValueDecimal(CustomerVarValueDecimal $itemVarValues)
    {
        $this->var_values_decimal[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_decimal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDecimal()
    {
        return $this->var_values_decimal;
    }

    /**
     * @param CustomerVarValueDatetime $itemVarValues
     * @return $this
     */
    public function addVarValueDatetime(CustomerVarValueDatetime $itemVarValues)
    {
        $this->var_values_datetime[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_datetime
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDatetime()
    {
        return $this->var_values_datetime;
    }

    /**
     * @param CustomerVarValueInt $itemVarValues
     * @return $this
     */
    public function addVarValueInt(CustomerVarValueInt $itemVarValues)
    {
        $this->var_values_int[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesInt()
    {
        return $this->var_values_int;
    }

    /**
     * @param CustomerVarValueText $itemVarValues
     * @return $this
     */
    public function addVarValueText(CustomerVarValueText $itemVarValues)
    {
        $this->var_values_text[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_text
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesText()
    {
        return $this->var_values_text;
    }

    /**
     * @param CustomerVarValueVarchar $itemVarValues
     * @return $this
     */
    public function addVarValueVarchar(CustomerVarValueVarchar $itemVarValues)
    {
        $this->var_values_varchar[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_varchar
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesVarchar()
    {
        return $this->var_values_varchar;
    }

    /**
     * @param $failedLogins
     * @return $this
     */
    public function setFailedLogins($failedLogins)
    {
        $this->failed_logins = $failedLogins;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailedLogins()
    {
        return $this->failed_logins;
    }

    /**
     * @param $lockedAt
     * @return $this
     */
    public function setLockedAt($lockedAt)
    {
        $this->locked_at = $lockedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLockedAt()
    {
        return $this->locked_at;
    }

    /**
     * @param $lastLoginAt
     * @return $this
     */
    public function setLastLoginAt($lastLoginAt)
    {
        $this->last_login_at = $lastLoginAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastLoginAt()
    {
        return $this->last_login_at;
    }

    /**
     * @param $apiKey
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->api_key = $apiKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->is_enabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param $isExpired
     * @return $this
     */
    public function setIsExpired($isExpired)
    {
        $this->is_expired = $isExpired;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExpired()
    {
        return $this->is_expired;
    }

    /**
     * @param $isLocked
     * @return $this
     */
    public function setIsLocked($isLocked)
    {
        $this->is_locked = $isLocked;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->is_locked;
    }

    /**
     * @param \DateTime $passwordUpdatedAt
     * @return $this
     */
    public function setPasswordUpdatedAt($passwordUpdatedAt)
    {
        $this->password_updated_at = $passwordUpdatedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordUpdatedAt()
    {
        return $this->password_updated_at;
    }

    /**
     * @param $isPasswordExpired
     * @return $this
     */
    public function setIsPasswordExpired($isPasswordExpired)
    {
        $this->is_password_expired = $isPasswordExpired;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPasswordExpired()
    {
        return $this->is_password_expired;
    }
}
