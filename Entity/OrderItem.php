<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\OrderItem
 *
 * @ORM\Table(name="order_item")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\OrderItemRepository")
 */
class OrderItem
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     *
     */
    protected $status;

    /**
     * @var string $tracking
     *
     * @ORM\Column(name="tracking", type="string", length=255, nullable=true)
     *
     */
    protected $tracking;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_picked", type="boolean", nullable=true)
     */
    protected $is_picked = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_packed", type="boolean", nullable=true)
     */
    protected $is_packed = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_shipped", type="boolean", nullable=true)
     */
    protected $is_shipped = false;

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
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="items")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * })
     */
    protected $order;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderShipment
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\OrderShipment", inversedBy="items")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_shipment_id", referencedColumnName="id")
     * })
     */
    protected $shipment;

    /**
     * @var integer $product_id
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    protected $product_id;

    /**
     * @var string $sku
     *
     * @ORM\Column(name="sku", type="string", length=255)
     */
    protected $sku;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

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
     * @var integer $qty
     *
     * @ORM\Column(name="qty", type="integer")
     */
    protected $qty;

    /**
     * @var float $weight
     *
     * @ORM\Column(name="weight", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $weight;

    /**
     * @var string $weight_unit
     *
     * @ORM\Column(name="weight_unit", type="string", length=8, nullable=true)
     */
    protected $weight_unit;

    /**
     * @var float $width
     *
     * @ORM\Column(name="width", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $width;

    /**
     * @var float $height
     *
     * @ORM\Column(name="height", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $height;

    /**
     * @var float $length
     *
     * @ORM\Column(name="length", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $length;

    /**
     * @var string $measure_unit
     *
     * @ORM\Column(name="measure_unit", type="string", length=8, nullable=true)
     */
    protected $measure_unit;

    /**
     * @var string $json
     *
     * @ORM\Column(name="json", type="text")
     */
    protected $json;

    public function __construct()
    {

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::ORDER_ITEM;
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
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'source_address_key' => $this->getSourceAddressKey(),
            'status' => $this->getStatus(),
            'tracking' => $this->getTracking(),
            'product_id' => $this->getProductId(),
            'sku' => $this->getSku(),
            'name' => $this->getName(),
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
            'qty' => $this->getQty(),
            'weight' => $this->getWeight(),
            'weight_unit' => $this->getWeightUnit(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'length' => $this->getLength(),
            'measure_unit' => $this->getMeasureUnit(),
        ];
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $Tracking
     * @return $this
     */
    public function setTracking($Tracking)
    {
        $this->tracking = $Tracking;
        return $this;
    }

    /**
     * @return string
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * Set product_id
     *
     * @param integer $productId
     */
    public function setProductId($productId)
    {
        $this->product_id = $productId;
    }

    /**
     * Get product_id
     *
     * @return integer 
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Set sku
     *
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * Get sku
     *
     * @return string 
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
     * Set qty
     *
     * @param integer $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * Get qty
     *
     * @return integer 
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param $weightUnit
     * @return $this
     */
    public function setWeightUnit($weightUnit)
    {
        $this->weight_unit = $weightUnit;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeightUnit()
    {
        return $this->weight_unit;
    }

    /**
     * @param $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param $measureUnit
     * @return $this
     */
    public function setMeasureUnit($measureUnit)
    {
        $this->measure_unit = $measureUnit;
        return $this;
    }

    /**
     * @return string
     */
    public function getMeasureUnit()
    {
        return $this->measure_unit;
    }

    /**
     * Set json
     *
     * @param string $json
     */
    public function setJson($json)
    {
        $this->json = $json;
    }

    /**
     * Get json
     *
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set order
     *
     * @param \MobileCart\CoreBundle\Entity\Order $saleOrder
     */
    public function setOrder(Order $saleOrder)
    {
        $this->order = $saleOrder;
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
     * @param OrderShipment $shipment
     * @return $this
     */
    public function setShipment(OrderShipment $shipment)
    {
        $this->shipment = $shipment;
        return $this;
    }

    /**
     * @return OrderShipment
     */
    public function getShipment()
    {
        return $this->shipment;
    }
}