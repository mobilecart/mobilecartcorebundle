<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\OrderVarValueText
 *
 * @ORM\Table(name="order_var_value_text")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CommonRepository")
 */
class OrderVarValueText
    implements CartEntityVarValueInterface
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
     * @var string $value
     *
     * @ORM\Column(name="value", type="text")
     */
    protected $value;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVar
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVar")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_id", referencedColumnName="id", nullable=false)
     * })
     */
    protected $item_var;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionText
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionText")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_option_id", referencedColumnName="id", nullable=true)
     * })
     */
    protected $item_var_option;
    
    /**
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="var_values_text")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $parent;

    public function __toString()
    {
        return $this->value;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param ItemVar $itemVar
     * @return $this
     */
    public function setItemVar(ItemVar $itemVar)
    {
        $this->item_var = $itemVar;
        return $this;
    }

    /**
     * Get item_var
     *
     * @return \MobileCart\CoreBundle\Entity\ItemVar
     */
    public function getItemVar()
    {
        return $this->item_var;
    }

    /**
     * @param $itemVarOption
     * @return $this
     */
    public function setItemVarOption($itemVarOption)
    {
        $this->item_var_option = $itemVarOption;
        return $this;
    }

    /**
     * @return mixed|ItemVarOptionText
     */
    public function getItemVarOption()
    {
        return $this->item_var_option;
    }

    /**
     * @param CartEntityEAVInterface $parent
     * @return $this
     */
    public function setParent(CartEntityEAVInterface $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return \MobileCart\CoreBundle\Entity\CartEntityEAVInterface
     */
    public function getParent()
    {
        return $this->parent;
    }
}