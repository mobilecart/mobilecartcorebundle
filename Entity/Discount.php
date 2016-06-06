<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\Discount
 *
 * @ORM\Table(name="discount")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\DiscountRepository")
 */
class Discount
    implements CartEntityInterface
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer $priority
     *
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var float $value
     *
     * @ORM\Column(name="value", type="decimal", precision=5, scale=2)
     */
    private $value;

    /**
     * @var string $applied_as
     *
     * @ORM\Column(name="applied_as", type="string", length=16)
     */
    private $applied_as;

    /**
     * @var string $applied_to
     *
     * @ORM\Column(name="applied_to", type="string", length=16)
     */
    private $applied_to;

    /**
     * @var boolean $is_pre_tax
     *
     * @ORM\Column(name="is_pre_tax", type="boolean", nullable=true)
     */
    private $is_pre_tax;

    /**
     * @var boolean $is_auto
     *
     * @ORM\Column(name="is_auto", type="boolean", nullable=true)
     */
    private $is_auto;
    
    /**
     * @var int $start_time
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     */
    private $start_time;
    
    /**
     * @var int $end_time
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     */
    private $end_time;

    /**
     * @var boolean $is_stopper
     *
     * @ORM\Column(name="is_stopper", type="boolean", nullable=true)
     */
    private $is_stopper;
    
    /**
     * @var boolean $is_compound
     *
     * @ORM\Column(name="is_compound", type="boolean", nullable=true)
     */
    private $is_compound;
    
    /**
     * @var boolean $is_proportional
     *
     * @ORM\Column(name="is_proportional", type="boolean", nullable=true)
     */
    private $is_proportional;
    
    /**
     * @var float $max_amount
     *
     * @ORM\Column(name="max_amount", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $max_amount;
    
    /**
     * @var float $max_qty
     *
     * @ORM\Column(name="max_qty", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $max_qty;
    
    /**
     * @var boolean $is_max_per_item
     *
     * @ORM\Column(name="is_max_per_item", type="boolean", nullable=true)
     */
    private $is_max_per_item;

    /**
     * @var string $coupon_code
     *
     * @ORM\Column(name="coupon_code", type="string", length=128, nullable=true)
     */
    private $coupon_code;

    /**
     * @var string $promo_skus
     *
     * @ORM\Column(name="promo_skus", type="text", nullable=true)
     */
    private $promo_skus;

    /**
     * @var string $pre_conditions
     *
     * @ORM\Column(name="pre_conditions", type="text", nullable=true)
     */
    private $pre_conditions;

    /**
     * @var string $target_conditions
     *
     * @ORM\Column(name="target_conditions", type="text", nullable=true)
     */
    private $target_conditions;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeName()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::DISCOUNT;
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
     * Getter , after fully loading
     *  use only if necessary, and avoid calling multiple times
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        $data = $this->getBaseData();

        if (strlen($key) > 0) {

            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getLuceneVarValuesData()
    {
        // Note:
        // be careful with adding foreign relationships here
        // since it will add 1 query every time an item is loaded

        return $this->getBaseData();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Discount
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'value' => $this->getValue(),
            'applied_as' => $this->getAppliedAs(),
            'applied_to' => $this->getAppliedTo(),
            'is_pre_tax' => $this->getIsPreTax(),
            'is_auto' => $this->getIsAuto(),
            'coupon_code' => $this->getCouponCode(),
            'priority' => $this->getPriority(),
            'is_stopper' => $this->getIsStopper(),
            'start_time' => $this->getStartTime(),
            'end_time' => $this->getEndTime(),
            'is_compound' => $this->getIsCompound(),
            'is_proportional' => $this->getIsProportional(),
            'max_amount' => $this->getMaxAmount(),
            'max_qty' => $this->getMaxQty(),
            'is_max_per_item' => $this->getIsMaxPerItem(),
            'promo_skus' => $this->getPromoSkus(),
            'pre_conditions' => $this->getPreConditions(),
            'target_conditions' => $this->getTargetConditions(),
        ];
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set applied_as
     *
     * @param string $appliedAs
     * @return $this
     */
    public function setAppliedAs($appliedAs)
    {
        $this->applied_as = $appliedAs;
        return $this;
    }

    /**
     * Get applied_as
     *
     * @return string 
     */
    public function getAppliedAs()
    {
        return $this->applied_as;
    }

    /**
     * Set applied_to
     *
     * @param string $appliedTo
     * @return $this
     */
    public function setAppliedTo($appliedTo)
    {
        $this->applied_to = $appliedTo;
        return $this;
    }

    /**
     * Get applied_to
     *
     * @return string 
     */
    public function getAppliedTo()
    {
        return $this->applied_to;
    }

    /**
     * Set is_pre_tax
     *
     * @param boolean $isPreTax
     * @return $this
     */
    public function setIsPreTax($isPreTax)
    {
        $this->is_pre_tax = $isPreTax;
        return $this;
    }

    /**
     * Get is_pre_tax
     *
     * @return boolean 
     */
    public function getIsPreTax()
    {
        return $this->is_pre_tax;
    }

    /**
     * Set is_auto
     *
     * @param boolean $isAuto
     * @return $this
     */
    public function setIsAuto($isAuto)
    {
        $this->is_auto = $isAuto;
        return $this;
    }

    /**
     * Get is_auto
     *
     * @return boolean 
     */
    public function getIsAuto()
    {
        return $this->is_auto;
    }

    /**
     * Set coupon_code
     *
     * @param string $couponCode
     * @return $this
     */
    public function setCouponCode($couponCode)
    {
        $this->coupon_code = $couponCode;
        return $this;
    }

    /**
     * Get coupon_code
     *
     * @return string 
     */
    public function getCouponCode()
    {
        return $this->coupon_code;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set is_stopper
     *
     * @param boolean $isStopper
     * @return $this
     */
    public function setIsStopper($isStopper)
    {
        $this->is_stopper = $isStopper;
        return $this;
    }

    /**
     * Get is_stopper
     *
     * @return boolean 
     */
    public function getIsStopper()
    {
        return $this->is_stopper;
    }

    /**
     * Set start_time
     *
     * @param string $startTime
     * @return $this
     */
    public function setStartTime($startTime)
    {
        $this->start_time = $startTime;
        return $this;
    }

    /**
     * Get start_time
     *
     * @return string
     */
    public function getStartTime()
    {
        return $this->start_time;
    }

    /**
     * Set end_time
     *
     * @param string $endTime
     * @return $this
     */
    public function setEndTime($endTime)
    {
        $this->end_time = $endTime;
        return $this;
    }

    /**
     * Get end_time
     *
     * @return string
     */
    public function getEndTime()
    {
        return $this->end_time;
    }

    /**
     * Set is_compound
     *
     * @param boolean $isCompound
     * @return $this
     */
    public function setIsCompound($isCompound)
    {
        $this->is_compound = $isCompound;
        return $this;
    }

    /**
     * Get is_compound
     *
     * @return boolean 
     */
    public function getIsCompound()
    {
        return $this->is_compound;
    }

    /**
     * Set is_proportional
     *
     * @param boolean $isProportional
     * @return $this
     */
    public function setIsProportional($isProportional)
    {
        $this->is_proportional = $isProportional;
        return $this;
    }

    /**
     * Get is_proportional
     *
     * @return boolean 
     */
    public function getIsProportional()
    {
        return $this->is_proportional;
    }

    /**
     * Set max_amount
     *
     * @param float $maxAmount
     * @return $this
     */
    public function setMaxAmount($maxAmount)
    {
        $this->max_amount = $maxAmount;
        return $this;
    }

    /**
     * Get max_amount
     *
     * @return float 
     */
    public function getMaxAmount()
    {
        return $this->max_amount;
    }

    /**
     * Set max_qty
     *
     * @param float $maxQty
     * @return $this
     */
    public function setMaxQty($maxQty)
    {
        $this->max_qty = $maxQty;
        return $this;
    }

    /**
     * Get max_qty
     *
     * @return float 
     */
    public function getMaxQty()
    {
        return $this->max_qty;
    }

    /**
     * Set is_max_per_item
     *
     * @param boolean $isMaxPerItem
     * @return $this
     */
    public function setIsMaxPerItem($isMaxPerItem)
    {
        $this->is_max_per_item = $isMaxPerItem;
        return $this;
    }

    /**
     * Get is_max_per_item
     *
     * @return boolean 
     */
    public function getIsMaxPerItem()
    {
        return $this->is_max_per_item;
    }

    /**
     * @param $promoSkus
     * @return $this
     */
    public function setPromoSkus($promoSkus)
    {
        $this->promo_skus = $promoSkus;
        return $this;
    }

    /**
     * @return string
     */
    public function getPromoSkus()
    {
        return $this->promo_skus;
    }

    /**
     * Set pre_conditions
     *
     * @param string $preConditions
     * @return $this
     */
    public function setPreConditions($preConditions)
    {
        $this->pre_conditions = $preConditions;
        return $this;
    }

    /**
     * Get pre_conditions
     *
     * @return string
     */
    public function getPreConditions()
    {
        return $this->pre_conditions;
    }

    /**
     * Set target_conditions
     *
     * @param string $targetConditions
     * @return $this
     */
    public function setTargetConditions($targetConditions)
    {
        $this->target_conditions = $targetConditions;
        return $this;
    }

    /**
     * Get target_conditions
     *
     * @return string
     */
    public function getTargetConditions()
    {
        return $this->target_conditions;
    }
}
