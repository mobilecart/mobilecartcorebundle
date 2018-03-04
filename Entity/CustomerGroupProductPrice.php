<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * MobileCart\CoreBundle\Entity\CustomerGroupProductPrice
 *
 * @ORM\Table(name="customer_group_product_price")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CommonRepository")
 */
class CustomerGroupProductPrice
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
     * @var float $price
     *
     * @ORM\Column(name="price", type="decimal", precision=12, scale=4)
     */
    protected $price;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    protected $currency;

    /**
     * @var \MobileCart\CoreBundle\Entity\Product
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Product", inversedBy="group_prices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $product;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\CustomerGroup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $customer_group;

    public function __toString()
    {
        return ''. $this->price;
    }

    /**
     * @return mixed|string
     */
    public function getObjectTypeKey()
    {
        return EntityConstants::CUSTOMER_GROUP_PRODUCT_PRICE;
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
            'price' => $this->getPrice(),
            'currency' => $this->getPrice(),
        ];
    }

    /**
     * @param $currency
     * @return Product
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
     * @param $price
     * @return Product
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
     * @param CustomerGroup $group
     * @return CustomerGroupProductPrice
     */
    public function setCustomerGroup(CustomerGroup $group)
    {
        $this->customer_group = $group;
        return $this;
    }

    /**
     * @return CustomerGroup
     */
    public function getCustomerGroup()
    {
        return $this->customer_group;
    }

    /**
     * Set product
     *
     * @param \MobileCart\CoreBundle\Entity\Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get product
     *
     * @return \MobileCart\CoreBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}