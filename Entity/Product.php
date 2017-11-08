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
    extends AbstractCartEntityEAV
    implements CartEntityEAVInterface, CartEntityImageParentInterface
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
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $created_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updated_at;

    /**
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    protected $old_id;

    /**
     * @var integer $sort_order
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sort_order;

    /**
     * @var boolean $is_public
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=true)
     */
    protected $is_public;

    /**
     * @var boolean $is_searchable
     *
     * @ORM\Column(name="is_searchable", type="boolean", nullable=true)
     */
    protected $is_searchable;

    /**
     * @var string $custom_template
     *
     * @ORM\Column(name="custom_template", type="string", length=255, nullable=true)
     */
    protected $custom_template;

    /**
     * @var string
     *
     * @ORM\Column(name="page_title", type="text", nullable=true)
     */
    protected $page_title;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    protected $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    protected $content;

    /**
     * @var string $meta_description
     *
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     */
    protected $meta_description;

    /**
     * @var string $meta_keywords
     *
     * @ORM\Column(name="meta_keywords", type="text", nullable=true)
     */
    protected $meta_keywords;

    /**
     * @var string $meta_title
     *
     * @ORM\Column(name="meta_title", type="text", nullable=true)
     */
    protected $meta_title;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductImage
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductImage", mappedBy="parent")
     */
    protected $images;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSet
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_set_id", referencedColumnName="id")
     * })
     */
    protected $item_var_set;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueDatetime
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueDatetime", mappedBy="parent")
     */
    protected $var_values_datetime;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueDecimal
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueDecimal", mappedBy="parent")
     */
    protected $var_values_decimal;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueInt
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueInt", mappedBy="parent")
     */
    protected $var_values_int;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueText
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueText", mappedBy="parent")
     */
    protected $var_values_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductVarValueVarchar
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductVarValueVarchar", mappedBy="parent")
     */
    protected $var_values_varchar;

    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer", nullable=true)
     */
    protected $type;

    /**
     * @var string $config
     *
     * @ORM\Column(name="config", type="text", nullable=true)
     */
    protected $config;

    /**
     * @var integer $visibility
     *
     * @ORM\Column(name="visibility", type="integer", nullable=true)
     */
    protected $visibility;

    /**
     * @var boolean $is_enabled
     *
     * @ORM\Column(name="is_enabled", type="boolean", nullable=true)
     */
    protected $is_enabled;

    /**
     * @var string $sku
     *
     * @ORM\Column(name="sku", type="string", length=255, unique=true)
     */
    protected $sku;

    /**
     * @var string $short_description
     *
     * @ORM\Column(name="short_description", type="text", nullable=true)
     */
    protected $short_description;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="decimal", precision=12, scale=4)
     */
    protected $price;

    /**
     * @var float $special_price
     *
     * @ORM\Column(name="special_price", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $special_price;

    /**
     * @var float $cost
     *
     * @ORM\Column(name="cost", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $cost;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    protected $currency;

    /**
     * @var \MobileCart\CoreBundle\Entity\CustomerGroupProductPrice
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CustomerGroupProductPrice", mappedBy="product")
     */
    protected $group_prices;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductTierPrice
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductTierPrice", mappedBy="product")
     */
    protected $tier_prices;

    /**
     * @var boolean $is_flat_shipping
     *
     * @ORM\Column(name="is_flat_shipping", type="boolean", nullable=true)
     */
    protected $is_flat_shipping;

    /**
     * @var float $flat_shipping_price
     *
     * @ORM\Column(name="flat_shipping_price", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $flat_shipping_price;

    /**
     * @var float $weight
     *
     * @ORM\Column(name="weight", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $weight;

    /**
     * @var string $weight_unit
     *
     * @ORM\Column(name="weight_unit", type="string", length=8, nullable=true)
     */
    protected $weight_unit;

    /**
     * @var float $width
     *
     * @ORM\Column(name="width", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $width;

    /**
     * @var float $height
     *
     * @ORM\Column(name="height", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $height;

    /**
     * @var float $length
     *
     * @ORM\Column(name="length", type="decimal", precision=12, scale=4, nullable=true)
     */
    protected $length;

    /**
     * @var string $measure_unit
     *
     * @ORM\Column(name="measure_unit", type="string", length=8, nullable=true)
     */
    protected $measure_unit;

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
     * @var boolean $is_in_stock
     *
     * @ORM\Column(name="is_in_stock", type="boolean", nullable=true)
     */
    protected $is_in_stock;

    /**
     * @var boolean $is_qty_managed
     *
     * @ORM\Column(name="is_qty_managed", type="boolean", nullable=true)
     */
    protected $is_qty_managed;

    /**
     * @var integer $stock_type
     *
     * @ORM\Column(name="stock_type", type="integer", nullable=true)
     */
    protected $stock_type;

    /**
     * @var string
     *
     * @ORM\Column(name="source_address_key", type="string", length=255, nullable=true)
     */
    protected $source_address_key;

    /**
     * @var string
     *
     * @ORM\Column(name="upc", type="string", length=255, nullable=true)
     */
    protected $upc;

    /**
     * @var boolean $can_backorder
     *
     * @ORM\Column(name="can_backorder", type="boolean", nullable=true)
     */
    protected $can_backorder;

    /**
     * @var integer $qty
     *
     * @ORM\Column(name="qty", type="integer")
     */
    protected $qty;

    /**
     * @var string $qty_unit
     *
     * @ORM\Column(name="qty_unit", type="string", length=32, nullable=true)
     */
    protected $qty_unit;

    /**
     * @var integer $min_qty
     *
     * @ORM\Column(name="min_qty", type="integer", nullable=true)
     */
    protected $min_qty;

    /**
     * @var string $fulltext_search
     *
     * @ORM\Column(name="fulltext_search", type="text", nullable=true)
     */
    protected $fulltext_search;

    /**
     * @var string $custom_search
     *
     * @ORM\Column(name="custom_search", type="text", nullable=true)
     */
    protected $custom_search;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryProduct $category_products
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryProduct", mappedBy="product")
     */
    protected $category_products;

    /**
     * @var \MobileCart\CoreBundle\Entity\ProductConfig
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ProductConfig", mappedBy="product")
     */
    protected $product_configs;

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

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::PRODUCT;
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
            'updated_at' => $this->getUpdatedAt(),
            'old_id' => $this->getOldId(),
            'custom_template' => $this->getCustomTemplate(),
            'sort_order' => $this->getSortOrder(),
            'is_public' => $this->getIsPublic(),
            'is_searchable' => $this->getIsSearchable(),
            'is_enabled' => $this->getIsEnabled(),
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
            'is_flat_shipping' => $this->getIsFlatShipping(),
            'flat_shipping_price' => $this->getFlatShippingPrice(),
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
            'is_in_stock' => $this->getIsInStock(),
            'is_discountable' => $this->getIsDiscountable(),
            'is_taxable' => $this->getIsTaxable(),
            'visibility' => $this->getVisibility(),
            'can_backorder' => $this->getCanBackorder(),
            'fulltext_search' => $this->getFulltextSearch(),
            'custom_search' => $this->getCustomSearch(),
        ];
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
        $data['item_var_set_id'] = $varSetId;

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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setIsPublic($isPublic)
    {
        $this->is_public = $isPublic;
        return $this;
    }

    /**
     * Get is_public
     *
     * @return bool
     */
    public function getIsPublic()
    {
        return (bool) $this->is_public;
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
     * @return bool
     */
    public function getIsSearchable()
    {
        return (bool) $this->is_searchable;
    }

    /**
     * Set name
     *
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
     * Set slug
     *
     * @param string $slug
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $config
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

    /**
     * Get visibility
     *
     * @return int
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->is_enabled = $isEnabled;
        return $this;
    }

    /**
     * Get is_enabled
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return (bool) $this->is_enabled;
    }

    /**
     * @param $sku
     * @return $this
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
     * @return $this
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
     * @param $price
     * @return $this
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
     * @return $this
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
     * @return bool
     */
    public function getIsTaxable()
    {
        return (bool) $this->is_taxable;
    }

    /**
     * @param CustomerGroupProductPrice $groupPrice
     * @return $this
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
     * @param $isFlatShipping
     * @return $this
     */
    public function setIsFlatShipping($isFlatShipping)
    {
        $this->is_flat_shipping = (bool) $isFlatShipping;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFlatShipping()
    {
        return (bool) $this->is_flat_shipping;
    }

    /**
     * @param $flatShippingPrice
     * @return $this
     */
    public function setFlatShippingPrice($flatShippingPrice)
    {
        $this->flat_shipping_price = $flatShippingPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getFlatShippingPrice()
    {
        return $this->flat_shipping_price;
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
     * @return $this
     */
    public function setIsDiscountable($isDiscountable)
    {
        $this->is_discountable = $isDiscountable;
        return $this;
    }

    /**
     * Get is_discountable
     *
     * @return bool
     */
    public function getIsDiscountable()
    {
        return (bool) $this->is_discountable;
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
     * @return bool
     */
    public function getIsInStock()
    {
        return (bool) $this->is_in_stock;
    }

    /**
     * @param $isQtyManaged
     * @return $this
     */
    public function setIsQtyManaged($isQtyManaged)
    {
        $this->is_qty_managed = $isQtyManaged;
        return $this;
    }

    /**
     * Get is_qty_managed
     *
     * @return bool
     */
    public function getIsQtyManaged()
    {
        return (bool) $this->is_qty_managed;
    }

    /**
     * @param $stockType
     * @return $this
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
     * @return $this
     */
    public function setCanBackorder($canBackorder)
    {
        $this->can_backorder = $canBackorder;
        return $this;
    }

    /**
     * Get can_backorder
     *
     * @return bool
     */
    public function getCanBackorder()
    {
        return $this->can_backorder;
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * Get qty
     *
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param $qtyUnit
     * @return $this
     */
    public function setQtyUnit($qtyUnit)
    {
        $this->qty_unit = $qtyUnit;
        return $this;
    }

    /**
     * Get qty
     *
     * @return int
     */
    public function getQtyUnit()
    {
        return $this->qty_unit;
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setMinQty($qty)
    {
        $this->min_qty = $qty;
        return $this;
    }

    /**
     * Get min_qty
     *
     * @return int
     */
    public function getMinQty()
    {
        return $this->min_qty;
    }

    /**
     * @param $fulltext_search
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return array
     */
    public function getCategories()
    {
        $categories = [];
        $categoryProducts = $this->getCategoryProducts();
        if ($categoryProducts) {
            foreach($categoryProducts as $categoryProduct) {
                $categories[] = $categoryProduct->getCategory();
            }
        }

        return $categories;
    }

    /**
     * @param ProductConfig $productConfig
     * @return $this
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
     * @return ArrayCollection|ProductTierPrice[]
     */
    public function getTierPrices()
    {
        return $this->tier_prices;
    }

    /**
     * @param ItemVarSet $itemVarSet
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @param CartEntityImageInterface $image
     * @return $this
     */
    public function addImage(CartEntityImageInterface $image)
    {
        $this->images[] = $image;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|ProductImage[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param $code
     * @param bool $isDefault
     * @return string
     */
    public function getImage($code, $isDefault = false)
    {
        $fallback = '';
        if ($this->images) {
            foreach($this->images as $image) {
                if ($image->getCode() == $code) {
                    if ($isDefault && $image->getIsDefault()) {
                        return $image;
                    } else {
                        $fallback = $image;
                    }

                }
            }
        }

        return $fallback;
    }

    /**
     * @param $code
     * @param bool $isDefault
     * @return mixed
     */
    public function getImagePath($code, $isDefault = false)
    {
        if ($image = $this->getImage($code, $isDefault)) {
            return $image->getPath();
        }
        return '';
    }
}
