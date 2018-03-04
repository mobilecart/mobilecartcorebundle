<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\ProductVarValueText
 *
 * @ORM\Table(name="product_var_value_text")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\VarValueRepository")
 */
class ProductVarValueText
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
     *   @ORM\JoinColumn(name="item_var_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $item_var;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionText
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionText")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_option_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $item_var_option;
    
    /**
     * @var \MobileCart\CoreBundle\Entity\Product
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Product", inversedBy="var_values_text")
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
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set value
     *
     * @param string $value
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
     * Set item_var
     *
     * @param \MobileCart\CoreBundle\Entity\ItemVar $itemVar
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
     * Get item_var_option
     *
     * @return \MobileCart\CoreBundle\Entity\ItemVarOption
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