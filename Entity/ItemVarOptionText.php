<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\ItemVarOptionText
 *
 * @ORM\Table(name="item_var_option_text")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ItemVarOptionTextRepository")
 */
class ItemVarOptionText
    extends AbstractCartEntity
    implements CartEntityInterface, CartEntityVarOptionInterface, ItemVarOptionInterface
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
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    protected $old_id;
    
    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVar
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVar", inversedBy="item_var_options_text")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $item_var;

    /**
     * @var string $value
     *
     * @ORM\Column(name="value", type="text")
     */
    protected $value;

    /**
     * @var string $url_value
     *
     * @ORM\Column(name="url_value", type="string", length=128)
     */
    protected $url_value;
    
    /**
     * @var boolean $is_in_stock
     *
     * @ORM\Column(name="is_in_stock", type="boolean", nullable=true)
     */
    protected $is_in_stock;

    /**
     * @var float $additional_price
     *
     * @ORM\Column(name="additional_price", type="decimal", nullable=true)
     */
    protected $additional_price;

    /**
     * @var integer $sort_order
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sort_order;

    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ITEM_VAR_OPTION_TEXT;
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
            'old_id' => $this->getOldId(),
            'item_var_id' => $this->getItemVar()->getId(),
            'item_var_name' => $this->getItemVar()->getName(),
            'value' => $this->getValue(),
            'url_value' => $this->getUrlValue(),
            'is_in_stock' => $this->getIsInStock(),
            'additional_price' => $this->getAdditionalPrice(),
            'sort_order' => $this->getSortOrder(),
        ];
    }

    /**
     * @param int $oldId
     * @return $this
     */
    public function setOldId($oldId)
    {
        $this->old_id = $oldId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOldId()
    {
        return $this->old_id;
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
     * @param $urlValue
     * @return $this
     */
    public function setUrlValue($urlValue)
    {
        $this->url_value = $urlValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlValue()
    {
        return $this->url_value;
    }

    /**
     * @param ItemVar $itemVar
     * @return $this
     */
    public function setItemVar(\MobileCart\CoreBundle\Entity\ItemVar $itemVar)
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
     * @param $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock)
    {
        $this->is_in_stock = $isInStock;
        return $this;
    }

    /**
     * Get is_in_stock
     *
     * @return boolean 
     */
    public function getIsInStock()
    {
        return $this->is_in_stock;
    }

    /**
     * @param $additionalPrice
     * @return $this
     */
    public function setAdditionalPrice($additionalPrice)
    {
        $this->additional_price = $additionalPrice;
        return $this;
    }

    /**
     * Get additional_price
     *
     * @return float 
     */
    public function getAdditionalPrice()
    {
        return $this->additional_price;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function setSortOrder($sort)
    {
        $this->sort_order = $sort;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }
}
