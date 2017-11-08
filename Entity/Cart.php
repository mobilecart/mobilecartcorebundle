<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CartRepository")
 */
class Cart
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
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $created_at;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     * })
     */
    protected $customer;

    /**
     * @var \MobileCart\CoreBundle\Entity\CartItem
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CartItem", mappedBy="cart")
     */
    protected $cart_items;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8, nullable=true)
     */
    protected $currency;

    /**
     * @var float $total
     *
     * @ORM\Column(name="total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $total;

    /**
     * @var float $item_total
     *
     * @ORM\Column(name="item_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $item_total;

    /**
     * @var float $tax_total
     *
     * @ORM\Column(name="tax_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $tax_total;

    /**
     * @var float $discount_total
     *
     * @ORM\Column(name="discount_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $discount_total;

    /**
     * @var float $shipping_total
     *
     * @ORM\Column(name="shipping_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $shipping_total;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8, nullable=true)
     */
    protected $base_currency;

    /**
     * @var float $base_total
     *
     * @ORM\Column(name="base_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_total;

    /**
     * @var float $base_item_total
     *
     * @ORM\Column(name="base_item_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_item_total;

    /**
     * @var float $base_tax_total
     *
     * @ORM\Column(name="base_tax_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_tax_total;

    /**
     * @var float $base_discount_total
     *
     * @ORM\Column(name="base_discount_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_discount_total;

    /**
     * @var float $base_shipping_total
     *
     * @ORM\Column(name="base_shipping_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $base_shipping_total;

    /**
     * @var string $json
     *
     * @ORM\Column(name="json", type="text")
     */
    protected $json;

    /**
     * @var boolean $is_wishlist
     *
     * @ORM\Column(name="is_wishlist", type="boolean", nullable=true)
     */
    protected $is_wishlist;

    public function __construct()
    {
        $this->cart_items = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CART;
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
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'currency' => $this->getCurrency(),
            'total' => $this->getTotal(),
            'item_total' => $this->getItemTotal(),
            'tax_total' => $this->getTaxTotal(),
            'discount_total' => $this->getDiscountTotal(),
            'shipping_total' => $this->getShippingTotal(),
            'base_currency' => $this->getBaseCurrency(),
            'base_total' => $this->getBaseTotal(),
            'base_item_total' => $this->getBaseItemTotal(),
            'base_tax_total' => $this->getBaseTaxTotal(),
            'base_discount_total' => $this->getBaseDiscountTotal(),
            'base_shipping_total' => $this->getBaseShippingTotal(),
            'is_wishlist' => $this->getIsWishlist(),
        ];
    }

    /**
     * Set json
     *
     * @param string $json
     * @return $this
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
     * Set is_wishlist
     *
     * @param boolean $isWishlist
     * @return $this
     */
    public function setIsWishlist($isWishlist)
    {
        $this->is_wishlist = $isWishlist;
        return $this;
    }

    /**
     * Get is_wishlist
     *
     * @return boolean 
     */
    public function getIsWishlist()
    {
        return $this->is_wishlist;
    }

    /**
     * Set customer
     *
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CartItem $cartItem
     * @return $this
     */
    public function addCartItem(CartItem $cartItem)
    {
        $this->cart_items[] = $cartItem;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|CartItem[]
     */
    public function getCartItems()
    {
        return $this->cart_items;
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
     * @param $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param $itemTotal
     * @return $this
     */
    public function setItemTotal($itemTotal)
    {
        $this->item_total = $itemTotal;
        return $this;
    }

    /**
     * Get item_total
     *
     * @return float
     */
    public function getItemTotal()
    {
        return $this->item_total;
    }

    /**
     * @param $taxTotal
     * @return $this
     */
    public function setTaxTotal($taxTotal)
    {
        $this->tax_total = $taxTotal;
        return $this;
    }

    /**
     * Get tax_total
     *
     * @return float
     */
    public function getTaxTotal()
    {
        return $this->tax_total;
    }

    /**
     * @param $discountTotal
     * @return $this
     */
    public function setDiscountTotal($discountTotal)
    {
        $this->discount_total = $discountTotal;
        return $this;
    }

    /**
     * Get discount_total
     *
     * @return float
     */
    public function getDiscountTotal()
    {
        return $this->discount_total;
    }

    /**
     * @param $shippingTotal
     * @return $this
     */
    public function setShippingTotal($shippingTotal)
    {
        $this->shipping_total = $shippingTotal;
        return $this;
    }

    /**
     * Get shipping_total
     *
     * @return float
     */
    public function getShippingTotal()
    {
        return $this->shipping_total;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setBaseCurrency($currency)
    {
        $this->base_currency = $currency;
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
     * @param $total
     * @return $this
     */
    public function setBaseTotal($total)
    {
        $this->base_total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getBaseTotal()
    {
        return $this->base_total;
    }

    /**
     * @param $itemTotal
     * @return $this
     */
    public function setBaseItemTotal($itemTotal)
    {
        $this->base_item_total = $itemTotal;
        return $this;
    }

    /**
     * Get item_total
     *
     * @return float
     */
    public function getBaseItemTotal()
    {
        return $this->base_item_total;
    }

    /**
     * @param $taxTotal
     * @return $this
     */
    public function setBaseTaxTotal($taxTotal)
    {
        $this->base_tax_total = $taxTotal;
        return $this;
    }

    /**
     * Get tax_total
     *
     * @return float
     */
    public function getBaseTaxTotal()
    {
        return $this->base_tax_total;
    }

    /**
     * @param $discountTotal
     * @return $this
     */
    public function setBaseDiscountTotal($discountTotal)
    {
        $this->base_discount_total = $discountTotal;
        return $this;
    }

    /**
     * Get discount_total
     *
     * @return float
     */
    public function getBaseDiscountTotal()
    {
        return $this->base_discount_total;
    }

    /**
     * @param $shippingTotal
     * @return $this
     */
    public function setBaseShippingTotal($shippingTotal)
    {
        $this->base_shipping_total = $shippingTotal;
        return $this;
    }

    /**
     * Get shipping_total
     *
     * @return float
     */
    public function getBaseShippingTotal()
    {
        return $this->base_shipping_total;
    }
}
