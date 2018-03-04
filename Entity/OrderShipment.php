<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderShipment
 *
 * @ORM\Table(name="order_shipment")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\OrderShipmentRepository")
 */
class OrderShipment
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $created_at;

    /**
     * @var string
     *
     * @ORM\Column(name="source_address_key", type="string", length=255, nullable=true)
     */
    protected $source_address_key;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255)
     */
    protected $company;

    /**
     * @var string
     *
     * @ORM\Column(name="method", type="string", length=255)
     */
    protected $method;

    /**
     * @var string
     *
     * @ORM\Column(name="tracking", type="text", nullable=true)
     */
    protected $tracking;

    /**
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="shipments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $order;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderItem
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderItem", mappedBy="shipment")
     */
    protected $items;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    protected $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=255, nullable=true)
     */
    protected $company_name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=24, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="street2", type="string", length=255, nullable=true)
     */
    protected $street2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     */
    protected $region;

    /**
     * @var string
     *
     * @ORM\Column(name="postcode", type="string", length=16, nullable=true)
     */
    protected $postcode;

    /**
     * @var string
     *
     * @ORM\Column(name="country_id", type="string", length=2, nullable=true)
     */
    protected $country_id;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="shipping_method_id", type="integer", nullable=true)
     */
    protected $shipping_method_id;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="decimal", precision=12, scale=4)
     */
    protected $price;

    /**
     * @var float $cost
     *
     * @ORM\Column(name="cost", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $cost;

    /**
     * @var float $tax
     *
     * @ORM\Column(name="tax", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $tax;

    /**
     * @var float $discount
     *
     * @ORM\Column(name="discount", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $discount;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    protected $currency;

    /**
     * @var float $base_price
     *
     * @ORM\Column(name="base_price", type="decimal", precision=12, scale=4)
     */
    protected $base_price;

    /**
     * @var float $base_cost
     *
     * @ORM\Column(name="base_cost", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_cost;

    /**
     * @var float $base_tax
     *
     * @ORM\Column(name="base_tax", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_tax;

    /**
     * @var float $base_discount
     *
     * @ORM\Column(name="base_discount", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_discount;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8)
     */
    protected $base_currency;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_paid", type="boolean", nullable=true)
     */
    protected $is_paid = false;

    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ORDER_SHIPMENT;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'source_address_key' => $this->getSourceAddressKey(),
            'company' => $this->getCompany(),
            'method' => $this->getMethod(),
            'tracking' => $this->getTracking(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'company_name' => $this->getCompany(),
            'phone' => $this->getPhone(),
            'street' => $this->getStreet(),
            'street2' => $this->getStreet2(),
            'city' => $this->getCity(),
            'region' => $this->getRegion(),
            'postcode' => $this->getPostcode(),
            'country_id' => $this->getCountryId(),
            'price' => $this->getPrice(),
            'cost' => $this->getCost(),
            'tax' => $this->getTax(),
            'discount' => $this->getDiscount(),
            'currency' => $this->getCurrency(),
            'base_price' => $this->getBasePrice(),
            'base_cost' => $this->getBaseCost(),
            'base_tax' => $this->getBaseTax(),
            'base_discount' => $this->getBaseDiscount(),
            'base_currency' => $this->getBaseCurrency(),
            'is_paid' => $this->getIsPaid(),
        ];
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
     * @param $srcAddressKey
     * @return $this
     */
    public function setSourceAddressKey($srcAddressKey)
    {
        $this->source_address_key = $srcAddressKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceAddressKey()
    {
        return $this->source_address_key;
    }

    /**
     * Set name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param $company
     * @return $this
     */
    public function setCompanyName($company)
    {
        $this->company_name = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company_name;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }
    
    /**
     * Set street
     *
     * @param string $street2
     * @return $this
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;
        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set country_id
     *
     * @param string $countryId
     * @return $this
     */
    public function setCountryId($countryId)
    {
        $this->country_id = $countryId;
        return $this;
    }

    /**
     * Get country_id
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->country_id;
    }

    /**
     * Set shipping_method_id
     *
     * @param int $shippingMethodId
     * @return $this
     */
    public function setShippingMethodId($shippingMethodId)
    {
        $this->shipping_method_id = $shippingMethodId;
        return $this;
    }

    /**
     * Get shipping_method_id
     *
     * @return int
     */
    public function getShippingMethodId()
    {
        return $this->shipping_method_id;
    }

    /**
     * Set tracking
     *
     * @param string $tracking
     * @return $this
     */
    public function setTracking($tracking)
    {
        $this->tracking = $tracking;

        return $this;
    }

    /**
     * Get tracking
     *
     * @return string 
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * @param $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set order
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \MobileCart\CoreBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Add order items
     *
     * @param OrderItem $item
     * @return $this
     */
    public function addItem(OrderItem $item)
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Get order items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param $cost
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param $tax
     * @return $this
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
        return $this;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param $discount
     * @return $this
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set base price
     *
     * @param float $price
     * @return $this
     */
    public function setBasePrice($price)
    {
        $this->base_price = $price;
        return $this;
    }

    /**
     * Get base price
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->base_price;
    }

    /**
     * @param $baseCost
     * @return $this
     */
    public function setBaseCost($baseCost)
    {
        $this->base_cost = $baseCost;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseCost()
    {
        return $this->base_cost;
    }

    /**
     * @param $tax
     * @return $this
     */
    public function setBaseTax($tax)
    {
        $this->base_tax = $tax;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseTax()
    {
        return $this->base_tax;
    }

    /**
     * @param $discount
     * @return $this
     */
    public function setBaseDiscount($discount)
    {
        $this->base_discount = $discount;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseDiscount()
    {
        return $this->base_discount;
    }

    /**
     * @param $baseCurrency
     * @return $this
     */
    public function setBaseCurrency($baseCurrency)
    {
        $this->base_currency = $baseCurrency;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->base_currency;
    }

    /**
     * @param $isPaid
     * @return $this
     */
    public function setIsPaid($isPaid)
    {
        $this->is_paid = (bool) $isPaid;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPaid()
    {
        return $this->is_paid;
    }
}
