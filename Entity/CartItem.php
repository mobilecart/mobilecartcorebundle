<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CartItem
 *
 * @ORM\Table(name="cart_item")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CartItemRepository")
 */
class CartItem
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    protected $product_id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $created_at;

    /**
     * @var \MobileCart\CoreBundle\Entity\Cart
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Cart", inversedBy="cart_items")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     * })
     */
    protected $cart;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255)
     */
    protected $sku;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="decimal", precision=12, scale=4)
     */
    protected $price;

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
     * @var int
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
     * @var string
     *
     * @ORM\Column(name="json", type="text", nullable=true)
     */
    protected $json;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_address_id", type="integer", nullable=true)
     */
    protected $customer_address_id;

    /**
     * Key of the Source Address, which is stored elsewhere
     *
     * @var string
     *
     * @ORM\Column(name="source_address_key", type="string", length=255)
     */
    protected $source_address_key;

    /**
     * @var string
     *
     * @ORM\Column(name="custom", type="text", nullable=true)
     */
    protected $custom;

    public function __construct()
    {
        $this->source_address_key = 'main';
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
        return \MobileCart\CoreBundle\Constants\EntityConstants::CART_ITEM;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'sku' => $this->getSku(),
            'product_id' => $this->getProductId(),
            'created_at' => $this->getCreatedAt(),
            'qty' => $this->getQty(),
            'currency' => $this->getCurrency(),
            'price' => $this->getPrice(),
            'tax' => $this->getTax(),
            'discount' => $this->getDiscount(),
            'base_currency' => $this->getBaseCurrency(),
            'base_price' => $this->getBasePrice(),
            'base_tax' => $this->getBaseTax(),
            'base_discount' => $this->getBaseDiscount(),
            'customer_address_id' => $this->getCustomerAddressId(),
            'weight' => $this->getWeight(),
            'weight_unit' => $this->getWeightUnit(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'length' => $this->getLength(),
            'measure_unit' => $this->getMeasureUnit(),
            'source_address_key' => $this->getSourceAddressKey(),
            'custom' => $this->getCustom(),
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
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->product_id = $productId;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return CartItem
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
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
     * @return CartItem
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty
     *
     * @return int
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
     * @return CartItem
     */
    public function setJson($json)
    {
        $this->json = $json;

        return $this;
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
     * @param $customerAddressId
     * @return $this
     */
    public function setCustomerAddressId($customerAddressId)
    {
        if ($customerAddressId == 'main') {
            $customerAddressId = null;
        }
        $this->customer_address_id = $customerAddressId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerAddressId()
    {
        return $this->customer_address_id;
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
     * @param string $custom
     * @return $this
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustom()
    {
        return $this->custom;
    }
}
