<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\ItemVar
 *
 * "Item Variants" aka Attributes in EAV Architecture
 *  are children of "Variant Sets"
 *  and are used for defining custom fields within Products, Categories, Orders, Content, etc
 *
 *
 * @ORM\Table(name="item_var")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ItemVarRepository")
 */
class ItemVar
    extends AbstractCartEntity
    implements CartEntityInterface
{
    const TYPE_DATETIME = 'datetime';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_INT = 'int';
    const TYPE_TEXT = 'text';
    const TYPE_VARCHAR = 'varchar';

    static $types = array(
        'datetime' => 'Datetime',
        'decimal' => 'Decimal',
        'int' => 'Integer',
        'text' => 'Text',
        'varchar' => 'Varchar',
    );

    static $formInputs = array(
        'text' => 'Text',
        'number' => 'Number',
        'date' => 'Date',
        'checkbox' => 'Checkbox',
        'select' => 'Select',
        'multiselect' => 'Multiselect',
    );

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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    protected $code;

    /**
     * @var string $url_token
     *
     * @ORM\Column(name="url_token", type="string", length=32)
     */
    protected $url_token;

    /**
     * @var string $datatype
     *
     * @ORM\Column(name="datatype", type="string", length=32)
     */
    protected $datatype;

    /**
     * @var string $form_input
     *
     * @ORM\Column(name="form_input", type="string", length=255, nullable=true)
     */
    protected $form_input;

    /**
     * @var boolean $is_required
     *
     * @ORM\Column(name="is_required", type="boolean", nullable=true)
     */
    protected $is_required;

    /**
     * @var boolean $is_displayed
     *
     * @ORM\Column(name="is_displayed", type="boolean", nullable=true)
     */
    protected $is_displayed;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sort_order;

    /**
     * @var boolean $is_facet
     *
     * @ORM\Column(name="is_facet", type="boolean", nullable=true)
     */
    protected $is_facet;

    /**
     * @var boolean $is_sortable
     *
     * @ORM\Column(name="is_sortable", type="boolean", nullable=true)
     */
    protected $is_sortable;

    /**
     * @var boolean $is_searchable
     *
     * @ORM\Column(name="is_searchable", type="boolean", nullable=true)
     */
    protected $is_searchable;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSetVar $item_var_set_vars
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSetVar", mappedBy="item_var")
     */
    protected $item_var_set_vars;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionDatetime
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionDatetime", mappedBy="item_var")
     */
    protected $item_var_options_datetime;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionDecimal
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionDecimal", mappedBy="item_var")
     */
    protected $item_var_options_decimal;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionInt
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionInt", mappedBy="item_var")
     */
    protected $item_var_options_int;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionText
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionText", mappedBy="item_var")
     */
    protected $item_var_options_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionVarchar
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionVarchar", mappedBy="item_var")
     */
    protected $item_var_options_varchar;

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->item_var_set_vars = new \Doctrine\Common\Collections\ArrayCollection();
        $this->item_var_options_datetime = new \Doctrine\Common\Collections\ArrayCollection();
        $this->item_var_options_decimal = new \Doctrine\Common\Collections\ArrayCollection();
        $this->item_var_options_int = new \Doctrine\Common\Collections\ArrayCollection();
        $this->item_var_options_text = new \Doctrine\Common\Collections\ArrayCollection();
        $this->item_var_options_varchar = new \Doctrine\Common\Collections\ArrayCollection();
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
        return \MobileCart\CoreBundle\Constants\EntityConstants::ITEM_VAR;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'old_id' => $this->getOldId(),
            'name' => $this->getName(),
            'code' => $this->getCode(),
            'url_token' => $this->getUrlToken(),
            'datatype' => $this->getDatatype(),
            'form_input' => $this->getFormInput(),
            'is_required' => $this->getIsRequired(),
            'is_displayed' => $this->getIsDisplayed(),
            'sort_order' => $this->getSortOrder(),
            'is_facet' => $this->getIsFacet(),
            'is_sortable' => $this->getIsSortable(),
            'is_searchable' => $this->getIsSearchable(),
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
     * @param string $name
     * @return $this
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
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $urlToken
     * @return $this
     */
    public function setUrlToken($urlToken)
    {
        $this->url_token = $urlToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlToken()
    {
        return $this->url_token;
    }

    /**
     * @param string $formInput
     * @return $this
     */
    public function setFormInput($formInput)
    {
        $this->form_input = $formInput;
        return $this;
    }

    /**
     * Get form input
     *
     * @return string
     */
    public function getFormInput()
    {
        return $this->form_input;
    }

    /**
     * @param string $datatype
     * @return $this
     */
    public function setDatatype($datatype)
    {
        $this->datatype = $datatype;
        return $this;
    }

    /**
     * Get datatype
     *
     * @return string 
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     * @param ItemVarSetVar $itemVarSetVar
     * @return $this
     */
    public function addItemVarSetVar(ItemVarSetVar $itemVarSetVar)
    {
        $this->item_var_set_vars[] = $itemVarSetVar;
        return $this;
    }

    /**
     * Get item var sets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItemVarSetVars()
    {
        return $this->item_var_set_vars;
    }

    /**
     * @param $itemVarOption
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addItemVarOption($itemVarOption)
    {
        if (!$this->getDatatype()) {
            throw new \InvalidArgumentException("Datatype is not specified.");
        }

        switch($this->getDatatype()) {
            case self::TYPE_DATETIME:
                $this->item_var_options_datetime[] = $itemVarOption;
                break;
            case self::TYPE_DECIMAL:
                $this->item_var_options_decimal[] = $itemVarOption;
                break;
            case self::TYPE_INT:
                $this->item_var_options_int[] = $itemVarOption;
                break;
            case self::TYPE_TEXT:
                $this->item_var_options_text[] = $itemVarOption;
                break;
            case self::TYPE_VARCHAR:
                $this->item_var_options_varchar[] = $itemVarOption;
                break;
            default:
                throw new \InvalidArgumentException("Invalid Datatype specified: '{$this->getDatatype()}'");
                break;
        }

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|ItemVarOptionDatetime|ItemVarOptionDecimal|ItemVarOptionInt|ItemVarOptionText|ItemVarOptionVarchar
     * @throws \InvalidArgumentException
     */
    public function getItemVarOptions()
    {
        if (!$this->getDatatype()) {
            throw new \InvalidArgumentException("Datatype is not specified.");
        }

        switch($this->getDatatype()) {
            case self::TYPE_DATETIME:
                return $this->item_var_options_datetime;
                break;
            case self::TYPE_DECIMAL:
                return $this->item_var_options_decimal;
                break;
            case self::TYPE_INT:
                return $this->item_var_options_int;
                break;
            case self::TYPE_TEXT:
                return $this->item_var_options_text;
                break;
            case self::TYPE_VARCHAR:
                return $this->item_var_options_varchar;
                break;
            default:
                throw new \InvalidArgumentException("Invalid Datatype specified: '{$this->getDatatype()}'");
                break;
        }
    }

    /**
     * @param $isDisplayed
     * @return $this
     */
    public function setIsDisplayed($isDisplayed)
    {
        $this->is_displayed = $isDisplayed;
        return $this;
    }

    /**
     * Get is_displayed
     *
     * @return boolean
     */
    public function getIsDisplayed()
    {
        return $this->is_displayed;
    }

    /**
     * @param $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired)
    {
        $this->is_required = $isRequired;
        return $this;
    }

    /**
     * Get is_required
     *
     * @return boolean
     */
    public function getIsRequired()
    {
        return $this->is_required;
    }

    /**
     * Set sort_order
     *
     * @param integer $sortOrder
     * @return CategoryProduct
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;
        return $this;
    }

    /**
     * Get sort_order
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param $isFacet
     * @return $this
     */
    public function setIsFacet($isFacet)
    {
        $this->is_facet = $isFacet;
        return $this;
    }

    /**
     * Get is_facet
     *
     * @return boolean
     */
    public function getIsFacet()
    {
        return $this->is_facet;
    }

    /**
     * @param $isSortable
     * @return $this
     */
    public function setIsSortable($isSortable)
    {
        $this->is_sortable = $isSortable;
        return $this;
    }

    /**
     * Get is_sortable
     *
     * @return boolean
     */
    public function getIsSortable()
    {
        return $this->is_sortable;
    }

    /**
     * @param $isSearchable
     * @return $this
     */
    public function setIsSearchable($isSearchable)
    {
        $this->is_searchable = $isSearchable;
        return $this;
    }

    /**
     * Get is_searchable
     *
     * @return boolean
     */
    public function getIsSearchable()
    {
        return $this->is_searchable;
    }
}
