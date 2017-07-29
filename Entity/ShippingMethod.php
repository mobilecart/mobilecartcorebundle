<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\ShippingMethod
 *
 * @ORM\Table(name="shipping_method")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ShippingMethodRepository")
 */
class ShippingMethod
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
     * @var string $company
     *
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    protected $company;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    /**
     * @var string $method
     *
     * @ORM\Column(name="method", type="string", length=255)
     */
    protected $method;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_days", type="integer", nullable=true)
     */
    protected $min_days;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_days", type="integer", nullable=true)
     */
    protected $max_days;

    /**
     * @var boolean $is_taxable
     *
     * @ORM\Column(name="is_taxable", type="boolean", nullable=true)
     */
    protected $is_taxable;

    /**
     * @var boolean $is_discountable
     *
     * @ORM\Column(name="is_discountable", type="boolean", nullable=true)
     */
    protected $is_discountable;

    /**
     * @var boolean $is_price_dynamic
     *
     * @ORM\Column(name="is_price_dynamic", type="boolean", nullable=true)
     */
    protected $is_price_dynamic;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="decimal", precision=5, scale=2, nullable=true)
     */
    protected $price;

    /**
     * @var string $pre_conditions
     *
     * @ORM\Column(name="pre_conditions", type="text", nullable=true)
     */
    protected $pre_conditions;

    /**
     * @var boolean $is_imported
     *
     * @ORM\Column(name="is_imported", type="boolean", nullable=true)
     */
    protected $is_imported;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::SHIPPING_METHOD;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getCompany() . '_' . $this->getMethod();
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'company' => $this->getCompany(),
            'method' => $this->getMethod(),
            'code' => $this->getCode(),
            'price' => $this->getPrice(),
            'min_days' => $this->getMinDays(),
            'max_days' => $this->getMaxDays(),
            'is_taxable' => $this->getIsTaxable(),
            'is_discountable' => $this->getIsDiscountable(),
            'is_price_dynamic' => $this->getIsPriceDynamic(),
            'pre_conditions' => $this->getPreConditions(),
        ];
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
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
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
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param $minDays
     * @return $this
     */
    public function setMinDays($minDays)
    {
        $this->min_days = $minDays;
        return $this;
    }

    /**
     * Get min days
     *
     * @return integer
     */
    public function getMinDays()
    {
        return $this->min_days;
    }

    /**
     * @param $maxDays
     * @return $this
     */
    public function setMaxDays($maxDays)
    {
        $this->max_days = $maxDays;
        return $this;
    }

    /**
     * Get max days
     *
     * @return integer
     */
    public function getMaxDays()
    {
        return $this->max_days;
    }

    /**
     * @param $isTaxable
     * @return $this
     */
    public function setIsTaxable($isTaxable)
    {
        $this->is_taxable = $isTaxable;
        return $this;
    }

    /**
     * Get is_taxable
     *
     * @return boolean 
     */
    public function getIsTaxable()
    {
        return $this->is_taxable;
    }

    /**
     * Set is_discountable
     *
     * @param boolean $isDiscountable
     */
    public function setIsDiscountable($isDiscountable)
    {
        $this->is_discountable = $isDiscountable;
    }

    /**
     * Get is_discountable
     *
     * @return boolean 
     */
    public function getIsDiscountable()
    {
        return $this->is_discountable;
    }

    /**
     * Set is_price_dynamic
     *
     * @param $isPriceDynamic
     * @return $this
     */
    public function setIsPriceDynamic($isPriceDynamic)
    {
        $this->is_price_dynamic = (bool) $isPriceDynamic;
        return $this;
    }

    /**
     * Get is_price_dynamic
     *
     * @return boolean
     */
    public function getIsPriceDynamic()
    {
        return (bool) $this->is_price_dynamic;
    }

    /**
     * Set price
     *
     * @param $price
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
     * Set pre_conditions
     *
     * @param $preConditions
     * @return $this
     */
    public function setPreconditions($preConditions)
    {
        $this->pre_conditions = $preConditions;
        return $this;
    }

    /**
     * Get pre_conditions
     *
     * @return string
     */
    public function getPreconditions()
    {
        return $this->pre_conditions;
    }

    /**
     * @param $isImported
     * @return $this
     */
    public function setIsImported($isImported)
    {
        $this->is_imported = $isImported;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsImported()
    {
        return $this->is_imported;
    }
}
