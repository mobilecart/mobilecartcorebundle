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
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Entity\ItemVarRepository")
 */
class ItemVar
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
    private $id;

    /**
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string $url_token
     *
     * @ORM\Column(name="url_token", type="string", length=32)
     */
    private $url_token;

    /**
     * @var string $datatype
     *
     * @ORM\Column(name="datatype", type="string", length=32)
     */
    private $datatype;

    /**
     * @var string $form_input
     *
     * @ORM\Column(name="form_input", type="string", length=255, nullable=true)
     */
    private $form_input;

    /**
     * @var boolean $is_required
     *
     * @ORM\Column(name="is_required", type="boolean", nullable=true)
     */
    private $is_required;

    /**
     * @var boolean $is_displayed
     *
     * @ORM\Column(name="is_displayed", type="boolean", nullable=true)
     */
    private $is_displayed;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSetVar $item_var_set_vars
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSetVar", mappedBy="item_var")
     */
    private $item_var_set_vars;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionDatetime
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionDatetime", mappedBy="item_var")
     */
    private $item_var_options_datetime;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionDecimal
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionDecimal", mappedBy="item_var")
     */
    private $item_var_options_decimal;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionInt
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionInt", mappedBy="item_var")
     */
    private $item_var_options_int;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionText
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionText", mappedBy="item_var")
     */
    private $item_var_options_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarOptionVarchar
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarOptionVarchar", mappedBy="item_var")
     */
    private $item_var_options_varchar;

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
        ];
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
}
