<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * MobileCart\CoreBundle\Entity\Product
 *
 * @ORM\Table(name="product", indexes={@ORM\Index(name="product_slug_idx", columns={"slug"})})
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ProductRepository")
 */
class Product
    implements CartEntityInterface, CartEntityEAVInterface
{
    // todo : add indexes to columns

    const TYPE_SIMPLE = 1;
    const TYPE_CONFIGURABLE = 2;

    static $types = [
        self::TYPE_SIMPLE => 'Simple',
        self::TYPE_CONFIGURABLE => 'Configurable',
    ];

    static function getTypes()
    {
        return self::$types;
    }

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
     * @var integer $sort_order
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sort_order;

    /**
     * @var boolean $is_public
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=true)
     */
    private $is_public;

    /**
     * @var boolean $is_searchable
     *
     * @ORM\Column(name="is_searchable", type="boolean", nullable=true)
     */
    private $is_searchable;

    /**
     * @var string $custom_template
     *
     * @ORM\Column(name="custom_template", type="string", length=255, nullable=true)
     */
    private $custom_template;

    /**
     * @var string
     *
     * @ORM\Column(name="page_title", type="text", nullable=true)
     */
    private $page_title;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string $meta_description
     *
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     */
    private $meta_description;

    /**
     * @var string $meta_keywords
     *
     * @ORM\Column(name="meta_keywords", type="text", nullable=true)
     */
    private $meta_keywords;

    /**
     * @var string $meta_title
     *
     * @ORM\Column(name="meta_title", type="text", nullable=true)
     */
    private $meta_title;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductImage
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductImage", mappedBy="parent")
     */
    private $images;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSet
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_set_id", referencedColumnName="id")
     * })
     */
    private $item_var_set;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueDatetime
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueDatetime", mappedBy="parent")
     */
    private $var_values_datetime;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueDecimal
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueDecimal", mappedBy="parent")
     */
    private $var_values_decimal;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueInt
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueInt", mappedBy="parent")
     */
    private $var_values_int;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueText
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueText", mappedBy="parent")
     */
    private $var_values_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueVarchar
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueVarchar", mappedBy="parent")
     */
    private $var_values_varchar;

    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer", nullable=true)
     */
    private $type;

    /**
     * @var string $config
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    private $config;

    /**
     * @var integer $visibility
     *
     * @ORM\Column(name="visibility", type="integer", nullable=true)
     */
    private $visibility;

    /**
     * @var boolean $is_enabled
     *
     * @ORM\Column(name="is_enabled", type="boolean", nullable=true)
     */
    private $is_enabled;

    /**
     * @var string $sku
     *
     * @ORM\Column(name="sku", type="string", length=255, unique=true)
     */
    private $sku;

    /**
     * @var string $short_description
     *
     * @ORM\Column(name="short_description", type="text", nullable=true)
     */
    private $short_description;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="decimal", precision=12, scale=4)
     */
    private $price;

    /**
     * @var float $special_price
     *
     * @ORM\Column(name="special_price", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $special_price;

    /**
     * @var float $cost
     *
     * @ORM\Column(name="cost", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $cost;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    private $currency;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerGroupProductPrice
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerGroupProductPrice", mappedBy="product")
     */
    private $group_prices;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductTierPrice
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductTierPrice", mappedBy="product")
     */
    private $tier_prices;

    /**
     * @var float $weight
     *
     * @ORM\Column(name="weight", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $weight;

    /**
     * @var string $weight_unit
     *
     * @ORM\Column(name="weight_unit", type="string", length=8, nullable=true)
     */
    private $weight_unit;

    /**
     * @var float $width
     *
     * @ORM\Column(name="width", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $width;

    /**
     * @var float $height
     *
     * @ORM\Column(name="height", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $height;

    /**
     * @var float $length
     *
     * @ORM\Column(name="length", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $length;

    /**
     * @var string $measure_unit
     *
     * @ORM\Column(name="measure_unit", type="string", length=8, nullable=true)
     */
    private $measure_unit;

    /**
     * @var boolean $is_taxable
     *
     * @ORM\Column(name="is_taxable", type="boolean", nullable=true)
     */
    private $is_taxable;

    /**
     * @var boolean $is_discountable
     *
     * @ORM\Column(name="is_discountable", type="boolean", nullable=true)
     */
    private $is_discountable;

    /**
     * @var boolean $is_in_stock
     *
     * @ORM\Column(name="is_in_stock", type="boolean", nullable=true)
     */
    private $is_in_stock;

    /**
     * @var boolean $is_qty_managed
     *
     * @ORM\Column(name="is_qty_managed", type="boolean", nullable=true)
     */
    private $is_qty_managed;

    /**
     * @var integer $stock_type
     *
     * @ORM\Column(name="stock_type", type="integer", nullable=true)
     */
    private $stock_type;

    /**
     * @var string
     *
     * @ORM\Column(name="source_address_key", type="string", length=255, nullable=true)
     */
    private $source_address_key;

    /**
     * @var string
     *
     * @ORM\Column(name="upc", type="string", length=255, nullable=true)
     */
    private $upc;

    /**
     * @var boolean $can_backorder
     *
     * @ORM\Column(name="can_backorder", type="boolean", nullable=true)
     */
    private $can_backorder;

    /**
     * @var integer $qty
     *
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;

    /**
     * @var string $qty_unit
     *
     * @ORM\Column(name="qty_unit", type="string", length=32, nullable=true)
     */
    private $qty_unit;

    /**
     * @var integer $min_qty
     *
     * @ORM\Column(name="min_qty", type="integer", nullable=true)
     */
    private $min_qty;

    /**
     * @var string $fulltext_search
     *
     * @ORM\Column(name="fulltext_search", type="text", nullable=true)
     */
    private $fulltext_search;

    /**
     * @var string $custom_search
     *
     * @ORM\Column(name="custom_search", type="text", nullable=true)
     */
    private $custom_search;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryProduct $category_products
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryProduct", mappedBy="product")
     */
    private $category_products;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductConfig
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductConfig", mappedBy="product")
     */
    private $product_configs;

    public function __construct()
    {
        $this->source_address_key = 'main'; // recommended key for main source address
        $this->group_prices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tier_prices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->product_configs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_datetime = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_decimal = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_int = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_text = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_varchar = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getSku();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::PRODUCT;
    }

    /**
     * @param $key
     * @param $value
     * @return Product
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
     * @return Product
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
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
            'old_id' => $this->getOldId(),
            'custom_template' => $this->getCustomTemplate(),
            'sort_order' => $this->getSortOrder(),
            'is_public' => (bool) $this->getIsPublic(),
            'is_searchable' => (bool) $this->getIsSearchable(),
            'is_enabled' => (bool) $this->getIsEnabled(),
            'page_title' => $this->getPageTitle(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'content' => $this->getContent(),
            'currency' => $this->getCurrency(),
            'meta_description' => $this->getMetaDescription(),
            'meta_keywords' => $this->getMetaKeywords(),
            'meta_title' => $this->getMetaTitle(),
            'type' => $this->getType(),
            'sku' => $this->getSku(),
            'price' => $this->getPrice(),
            'special_price' => $this->getSpecialPrice(),
            'stock_type' => $this->getStockType(),
            'source_address_key' => $this->getSourceAddressKey(),
            'qty' => $this->getQty(),
            'qty_unit' => $this->getQtyUnit(),
            'min_qty' => $this->getMinQty(),
            'weight' => $this->getWeight(),
            'weight_unit' => $this->getWeightUnit(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'length' => $this->getLength(),
            'measure_unit' => $this->getMeasureUnit(),
            'is_in_stock' => (bool) $this->getIsInStock(),
            'is_discountable' => (bool) $this->getIsDiscountable(),
            'is_taxable' => (bool) $this->getIsTaxable(),
            'visibility' => $this->getVisibility(),
            'can_backorder' => $this->getCanBackorder(),
            'fulltext_search' => $this->getFulltextSearch(),
            'custom_search' => $this->getCustomSearch(),
        ];
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
     * Get All Data or specific key of data
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        if (strlen($key) > 0) {

            $data = $this->getBaseData();
            if (isset($data[$key])) {
                return $data[$key];
            }

            $data = $this->getVarValuesData();
            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return array_merge($this->getVarValuesData(), $this->getBaseData());
    }

    /**
     * @return array
     */
    public function getLuceneVarValuesData()
    {
        // Note:
        // be careful with adding foreign relationships here
        // since it will add 1 query every time an item is loaded

        $pData = $this->getBaseData();

        $varValues = $this->getVarValues();
        $configValues = $this->getConfigVarValues();
        if ($configValues->count()) {
            foreach($configValues as $configValue) {
                $varValues->add($configValue);
            }
        }

        if (!$varValues->count()) {
            return $pData;
        }

        foreach($varValues as $itemVarValue) {

            /** @var ItemVar $itemVar */
            $itemVar = $itemVarValue->getItemVar();

            $value = $itemVarValue->getValue();
            switch($itemVar->getDatatype()) {
                case 'int':
                    $value = (int) $value;
                    break;
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'datetime':
                    // for Lucene
                    // todo: add a flag for this
                    $value = gmdate('Y-m-d\TH:i:s\Z', strtotime($value));
                    break;
                default:
                    $value = (string) $value;
                    break;
            }

            if ($itemVar->getFormInput() == 'multiselect') {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = [];
                }
                $data[$itemVar->getCode()][] = $value;
            } else {
                $data[$itemVar->getCode()] = $value;
            }

        }

        return array_merge($this->getVarValuesData(), $pData);
    }

    /**
     * Get Var Values as associative Array
     *
     * @return array
     */
    public function getVarValuesData()
    {
        $varSet = $this->getItemVarSet();
        $varSetId = ($varSet instanceof ItemVarSet)
            ? $varSet->getId()
            : null;

        $data = $this->getBaseData();
        $data['var_set_id'] = $varSetId;

        $varValues = $this->getVarValues();
        if (!$varValues) {
            return $data;
        }

        foreach($varValues as $itemVarValue) {

            /** @var ItemVar $itemVar */
            $itemVar = $itemVarValue->getItemVar();

            $value = $itemVarValue->getValue();
            switch($itemVar->getDatatype()) {
                case 'int':
                    $value = (int) $value;
                    break;
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'datetime':
                    $value = gmdate('Y-m-d H:i:s', strtotime($value));
                    break;
                default:
                    $value = (string) $value;
                    break;
            }

            // Configurable products need to have a multiselect on variants which are used to configure the product
            //  each of the stored values will be values from simple products
            if ($itemVar->getFormInput() == EntityConstants::INPUT_MULTISELECT
                || ($itemVar->getFormInput() == EntityConstants::INPUT_SELECT && $this->getType() == self::TYPE_CONFIGURABLE)
            ) {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = [];
                }
                $data[$itemVar->getCode()][] = $value;
            } else {
                $data[$itemVar->getCode()] = $value;
            }

        }

        return $data;
    }

    /**
     *
     * @return array
     */
    public function getVarValues()
    {
        $values = new ArrayCollection();
        $datetimes = $this->getVarValuesDatetime();
        $decimals = $this->getVarValuesDecimal();
        $ints = $this->getVarValuesInt();
        $texts = $this->getVarValuesText();
        $varchars = $this->getVarValuesVarchar();

        if ($datetimes) {
            foreach($datetimes as $value) {
                $values->add($value);
            }
        }

        if ($decimals) {
            foreach($decimals as $value) {
                $values->add($value);
            }
        }

        if ($ints) {
            foreach($ints as $value) {
                $values->add($value);
            }
        }

        if ($texts) {
            foreach($texts as $value) {
                $values->add($value);
            }
        }

        if ($varchars) {
            foreach($varchars as $value) {
                $values->add($value);
            }
        }

        return $values;
    }

    /**
     * @return ArrayCollection
     */
    public function getConfigVarValues()
    {
        $values = new ArrayCollection();
        if ($this->getType() == self::TYPE_CONFIGURABLE) {
            $configValues = $this->getProductConfigs();
            $usedSimpleIds = [];
            if ($configValues->count()) {
                foreach($configValues as $configValue) {
                    if (!in_array($configValue->getChildProduct()->getId(), $usedSimpleIds)) {
                        $simpleValues = $configValue->getChildProduct()->getVarValues();
                        if ($simpleValues->count()) {
                            foreach($simpleValues as $simpleValue) {
                                $values->add($simpleValue);
                                $usedSimpleIds[] = $configValue->getChildProduct()->getId();
                            }
                        }
                    }
                }
            }
        }
        return $values;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Product
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Product
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param int $oldId
     * @return Product
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
     * @return bool
     */
    public function canAddToCart()
    {
        return ($this->getIsInStock() || $this->getCanBackorder());
    }

    /**
     * @return bool
     */
    public function isOnBackorder()
    {
        return ($this->getQty() < 1 && $this->getCanBackorder());
    }

    /**
     * @param $customTemplate
     * @return Product
     */
    public function setCustomTemplate($customTemplate)
    {
        $this->custom_template = $customTemplate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomTemplate()
    {
        return $this->custom_template;
    }

    /**
     * @param $title
     * @return Product
     */
    public function setPageTitle($title)
    {
        $this->page_title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->page_title;
    }

    /**
     * @param $sortOrder
     * @return Product
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param $isPublic
     * @return Product
     */
    public function setIsPublic($isPublic)
    {
        $this->is_public = $isPublic;
        return $this;
    }

    /**
     * Get is_public
     *
     * @return boolean
     */
    public function getIsPublic()
    {
        return $this->is_public;
    }

    /**
     * @param $isSearchable
     * @return Product
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

    /**
     * Set name
     *
     * @param string $name
     * @return Product
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
     * Set slug
     *
     * @param string $slug
     * @return Product
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Product
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $desc
     * @return Product
     */
    public function setMetaDescription($desc)
    {
        $this->meta_description = $desc;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->meta_description;
    }

    /**
     * @param $desc
     * @return Product
     */
    public function setMetaKeywords($desc)
    {
        $this->meta_keywords = $desc;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->meta_keywords;
    }

    /**
     * @param $desc
     * @return Product
     */
    public function setMetaTitle($desc)
    {
        $this->meta_title = $desc;
        return $this;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->meta_title;
    }

    /**
     * @param $type
     * @return Product
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $config
     * @return Product
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        if (!is_int(strpos($this->config, '{'))) {
            return '{}';
        }
        return $this->config;
    }

    /**
     * @return Product
     */
    public function reconfigure()
    {
        /*
        {"config_values":[
         {
            "var_code":"size",
            "label":"Size",
            "product_values":[
              {
                "value":"blue",
                "products":[231,232,233]
              },
              {
                "value":"green",
                "products":[232,233,234]
               }
            ]
         },
         {
            "var_code":"color",
            "label":"Color",
            "product_values":[
            ....
            ....
         ]}
        ]}
        //*/

        //pull rows from product_config
        $varValues = [
            'config_values' => [], //only configurable products
            'is_in_stock' => [], //only productIds which are in stock
            'stock' => [], //all stock information for all products
            'options' => [], //only for simple products with simple options
            'group_values' => [], //only grouped products
            'bundle_values' => [], //only bundled products
        ];

        $tmpStock = [];
        $tmpValues = [];
        $tmpLabels = [];

        $configs = $this->getProductConfigs();
        if ($configs->count()) {
            foreach($configs as $config) {
                /** @var ItemVar $itemVar */
                $itemVar = $config->getItemVar();
                $varCode = $itemVar->getCode();

                /** @var Product $childProduct */
                $childProduct = $config->getChildProduct();
                $childProductId = $childProduct->getId();
                $isInStock = $childProduct->getIsInStock();
                $childVarValues = $childProduct->getVarValues();
                $childData = [];
                if ($childVarValues->count()) {
                    foreach($childVarValues as $varValue) {
                        if ($varValue->getItemVar()->getCode() == $varCode) {
                            if (!isset($childData[$varCode])) {
                                $childData[$varCode] = $varValue->getValue();
                            } else if (is_array($childData[$varCode])) {
                                $childData[$varCode][] = $varValue->getValue();
                            } else {

                                $oldValue = isset($childData[$varCode])
                                    ? $childData[$varCode]
                                    : null;

                                if (is_null($oldValue)) {
                                    $childData[$varCode] = [$varValue->getValue()];
                                } else {
                                    $childData[$varCode] = [
                                        $varValue->getValue(),
                                        $oldValue
                                    ];
                                }
                            }

                            if (!isset($tmpLabels[$varCode])) {
                                $tmpLabels[$varCode] = $varValue->getItemVar()->getName();
                            }
                        }
                    }
                }

                if (!isset($tmpStock[$childProductId])) {
                    $tmpStock[$childProductId] = [
                        'product_id'     => $childProductId,
                        'is_in_stock'    => $isInStock,
                        'qty'            => $childProduct->getQty(),
                        'is_qty_managed' => $childProduct->getIsQtyManaged(),
                        'can_backorder'  => $childProduct->getCanBackorder(),
                    ];
                }

                if ($isInStock && !in_array($childProductId, $varValues['is_in_stock'])) {
                    $varValues['is_in_stock'][] = $childProductId;
                }

                if (!isset($tmpValues[$varCode])) {
                    $tmpValues[$varCode] = [];
                }

                if (!isset($childData[$varCode])) {
                    continue;
                } elseif (is_array($childData[$varCode])) {
                    foreach($childData[$varCode] as $value) {

                        if (!isset($tmpValues[$varCode][$value])) {
                            $tmpValues[$varCode][$value] = [];
                        }

                        if (!in_array($childProductId, $tmpValues[$varCode][$value])) {
                            $tmpValues[$varCode][$value][] = $childProductId;
                        }
                    }
                } elseif (isset($childData[$varCode])) {

                    $value = $childData[$varCode];

                    if (!isset($tmpValues[$varCode][$value])) {
                        $tmpValues[$varCode][$value] = [];
                    }

                    if (!in_array($childProductId, $tmpValues[$varCode][$value])) {
                        $tmpValues[$varCode][$value][] = $childProductId;
                    }
                }
            }
        }

        $varValues['stock'] = array_values($tmpStock);

        if ($tmpValues) {
            foreach($tmpValues as $varCode => $varCodeValues) {

                $label = isset($tmpLabels[$varCode])
                    ? $tmpLabels[$varCode]
                    : $varCode;

                $data = [
                    'var_code' => $varCode,
                    'label' => $label,
                    'product_values' => [],
                ];

                foreach($varCodeValues as $varValue => $productIds) {
                    $data['product_values'][] = [
                        'value'    => $varValue,
                        'products' => $productIds,
                    ];
                }
                $varValues['config_values'][] = $data;
            }
        }

        //set config and save
        $this->setConfig(json_encode($varValues));
        return $this;
    }

    /**
     * @param $visibility
     * @return Product
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * Get visibility
     *
     * @return integer
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param $isEnabled
     * @return Product
     */
    public function setIsEnabled($isEnabled)
    {
        $this->is_enabled = $isEnabled;
        return $this;
    }

    /**
     * Get is_enabled
     *
     * @return boolean
     */
    public function getIsEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param $sku
     * @return Product
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
     * @param $upc
     * @return $this
     */
    public function setUpc($upc)
    {
        $this->upc = $upc;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpc()
    {
        return $this->upc;
    }

    /**
     * @param $desc
     * @return Product
     */
    public function setShortDescription($desc)
    {
        $this->short_description = $desc;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
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
     * @param $price
     * @return Product
     */
    public function setSpecialPrice($price)
    {
        $this->special_price = $price;
        return $this;
    }

    /**
     * Get special price
     *
     * @return float
     */
    public function getSpecialPrice()
    {
        return $this->special_price;
    }

    /**
     * @param $cost
     * @return Product
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param $isTaxable
     * @return Product
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
     * @param CustomerGroupProductPrice $groupPrice
     * @return Product
     */
    public function addGroupPrice(CustomerGroupProductPrice $groupPrice)
    {
        $this->group_prices[] = $groupPrice;
        return $this;
    }

    /**
     * Get group_prices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupPrices()
    {
        return $this->group_prices;
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
     * @param $isDiscountable
     * @return Product
     */
    public function setIsDiscountable($isDiscountable)
    {
        $this->is_discountable = $isDiscountable;
        return $this;
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
     * @param $isInStock
     * @return Product
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
     * @param $isQtyManaged
     * @return Product
     */
    public function setIsQtyManaged($isQtyManaged)
    {
        $this->is_qty_managed = $isQtyManaged;
        return $this;
    }

    /**
     * Get is_qty_managed
     *
     * @return boolean
     */
    public function getIsQtyManaged()
    {
        return $this->is_qty_managed;
    }

    /**
     * @param $stockType
     * @return Product
     */
    public function setStockType($stockType)
    {
        $this->stock_type = $stockType;
        return $this;
    }

    /**
     * Get stock_type
     *
     * @return int
     */
    public function getStockType()
    {
        return $this->stock_type;
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
     * @param $canBackorder
     * @return Product
     */
    public function setCanBackorder($canBackorder)
    {
        $this->can_backorder = $canBackorder;
        return $this;
    }

    /**
     * Get can_backorder
     *
     * @return boolean 
     */
    public function getCanBackorder()
    {
        return $this->can_backorder;
    }

    /**
     * @param $qty
     * @return Product
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * Get qty
     *
     * @return integer 
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param $qtyUnit
     * @return Product
     */
    public function setQtyUnit($qtyUnit)
    {
        $this->qty_unit = $qtyUnit;
        return $this;
    }

    /**
     * Get qty
     *
     * @return integer
     */
    public function getQtyUnit()
    {
        return $this->qty_unit;
    }

    /**
     * @param $qty
     * @return Product
     */
    public function setMinQty($qty)
    {
        $this->min_qty = $qty;
        return $this;
    }

    /**
     * Get min_qty
     *
     * @return integer
     */
    public function getMinQty()
    {
        return $this->min_qty;
    }

    /**
     * @param $fulltext_search
     * @return Product
     */
    public function setFulltextSearch($fulltext_search)
    {
        $this->fulltext_search = $fulltext_search;
        return $this;
    }

    /**
     * Get search
     *
     * @return string
     */
    public function getFulltextSearch()
    {
        return $this->fulltext_search;
    }

    /**
     * @param $customSearch
     * @return Product
     */
    public function setCustomSearch($customSearch)
    {
        $this->custom_search = $customSearch;
        return $this;
    }

    /**
     * Get custom_search
     *
     * @return string
     */
    public function getCustomSearch()
    {
        return $this->custom_search;
    }

    /**
     * @param CategoryProduct $categoryProduct
     * @return Product
     */
    public function addCategoryProduct(CategoryProduct $categoryProduct)
    {
        $this->category_products[] = $categoryProduct;
        return $this;
    }

    /**
     * @return CategoryProduct
     */
    public function getCategoryProducts()
    {
        return $this->category_products;
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        $categoryIds = [];
        $categoryProducts = $this->getCategoryProducts();
        if ($categoryProducts) {
            foreach($categoryProducts as $categoryProduct) {
                $categoryIds[] = $categoryProduct->getCategoryId();
            }
        }

        return $categoryIds;
    }

    /**
     * @param ProductConfig $productConfig
     * @return Product
     */
    public function addProductConfig(ProductConfig $productConfig)
    {
        $this->product_configs[] = $productConfig;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|ProductConfig
     */
    public function getProductConfigs()
    {
        return $this->product_configs;
    }

    /**
     * @param array $productConfigs
     * @return $this
     */
    public function setProductConfigs(array $productConfigs)
    {
        $this->product_configs = new \Doctrine\Common\Collections\ArrayCollection();
        if (!$productConfigs) {
            return $this;
        }

        foreach($productConfigs as $pConfig) {
            if (!($pConfig instanceof ProductConfig)) {
                continue;
            }

            $this->product_configs[] = $pConfig;
        }

        return $this;
    }

    /**
     * @param ProductTierPrice $tierPrice
     * @return $this
     */
    public function addTierPrice(ProductTierPrice $tierPrice)
    {
        $this->tier_prices[] = $tierPrice;
        return $this;
    }

    /**
     * @return ArrayCollection|ProductTierPrice
     */
    public function getTierPrices()
    {
        return $this->tier_prices;
    }

    /**
     * @param ItemVarSet $itemVarSet
     * @return Product
     */
    public function setItemVarSet(ItemVarSet $itemVarSet)
    {
        $this->item_var_set = $itemVarSet;
        return $this;
    }

    /**
     * Get item_var_set
     *
     * @return \MobileCart\CoreBundle\Entity\ItemVarSet
     */
    public function getItemVarSet()
    {
        return $this->item_var_set;
    }

    /**
     * @param ProductVarValueDecimal $itemVarValues
     * @return Product
     */
    public function addVarValueDecimal(ProductVarValueDecimal $itemVarValues)
    {
        $this->var_values_decimal[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_decimal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDecimal()
    {
        return $this->var_values_decimal;
    }

    /**
     * @param ProductVarValueDatetime $itemVarValues
     * @return Product
     */
    public function addVarValueDatetime(ProductVarValueDatetime $itemVarValues)
    {
        $this->var_values_datetime[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_datetime
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDatetime()
    {
        return $this->var_values_datetime;
    }

    /**
     * @param ProductVarValueInt $itemVarValues
     * @return Product
     */
    public function addVarValueInt(ProductVarValueInt $itemVarValues)
    {
        $this->var_values_int[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesInt()
    {
        return $this->var_values_int;
    }

    /**
     * @param ProductVarValueText $itemVarValues
     * @return Product
     */
    public function addVarValueText(ProductVarValueText $itemVarValues)
    {
        $this->var_values_text[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_text
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesText()
    {
        return $this->var_values_text;
    }

    /**
     * @param ProductVarValueVarchar $itemVarValues
     * @return Product
     */
    public function addVarValueVarchar(ProductVarValueVarchar $itemVarValues)
    {
        $this->var_values_varchar[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_varchar
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesVarchar()
    {
        return $this->var_values_varchar;
    }

    /**
     * @param ProductImage $image
     * @return Product
     */
    public function addImage(ProductImage $image)
    {
        $this->images[] = $image;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param $code
     * @return string
     */
    public function getImage($code)
    {
        if ($this->images) {
            foreach($this->images as $image) {
                if ($image->getCode() == $code) {
                    return $image;
                }
            }
        }

        return '';
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getImagePath($code)
    {
        if ($image = $this->getImage($code)) {
            return $image->getPath();
        }
        return '';
    }
}
