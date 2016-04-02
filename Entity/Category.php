<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * MobileCart\CoreBundle\Entity\Category
 *
 * @ORM\Table(name="category", indexes={@ORM\Index(name="category_slug_idx", columns={"slug"})})
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Entity\CategoryRepository")
 */
class Category
    implements CartEntityInterface, CartEntityEAVInterface
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
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

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
     * @var \MobileCart\CoreBundle\Entity\CategoryImage
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryImage", mappedBy="parent")
     */
    private $images;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryProduct $category_products
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryProduct", mappedBy="category")
     */
    private $category_products;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="child_categories")
     * @ORM\JoinColumn(name="parent_category_id", referencedColumnName="id")
     */
    private $parent_category;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent_category")
     */
    private $child_categories;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSet
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_set_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $item_var_set;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryVarValueDatetime
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryVarValueDatetime", mappedBy="parent")
     */
    private $var_values_datetime;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryVarValueDecimal
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryVarValueDecimal", mappedBy="parent")
     */
    private $var_values_decimal;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryVarValueInt
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryVarValueInt", mappedBy="parent")
     */
    private $var_values_int;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryVarValueText
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryVarValueText", mappedBy="parent")
     */
    private $var_values_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\CategoryVarValueVarchar
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CategoryVarValueVarchar", mappedBy="parent")
     */
    private $var_values_varchar;

    public function __construct()
    {
        $this->child_categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_datetime = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_decimal = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_int = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_text = new \Doctrine\Common\Collections\ArrayCollection();
        $this->var_values_varchar = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeName()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CATEGORY;
    }

    /**
     * @param $key
     * @param $value
     * @return Category
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
     * @param array $data
     * @return Category
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
        $data = array_merge($this->getVarValuesData(), $this->getBaseData());

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

        $pData = $this->getBaseData();

        $varValues = $this->getVarValues();
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
                    $value = gmdate('Y-m-d\TH:i:s\Z', strtotime($value));
                    break;
                default:
                    $value = (string) $value;
                    break;
            }

            if ($itemVar->getFormInput() == 'multiselect') {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = array();
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
        //$data['tags'] = $this->getTagsData();

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

            if ($itemVar->getFormInput() == 'multiselect') {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = array();
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
     * @return array
     */
    public function getBaseData()
    {
        $parentCategoryId = '';
        if ($this->getParentCategory()) {
            $parentCategoryId = $this->getParentCategory()->getId();
        }

        $images = [];
        if ($imageObjects = $this->getImages()) {
            foreach($imageObjects as $imageObject) {
                $images[] = $imageObject->getData();
            }
        }

        return [
            'id' => $this->getId(),
            'parent_category_id' => $parentCategoryId,
            'created_at' => $this->getCreatedAt(),
            'old_id' => $this->getOldId(),
            'sort_order' => $this->getSortOrder(),
            'custom_template' => $this->getCustomTemplate(),
            'is_public' => $this->getIsPublic(),
            'is_searchable' => $this->getIsSearchable(),
            'page_title' => $this->getPageTitle(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'content' => $this->getContent(),
            'meta_description' => $this->getMetaDescription(),
            'meta_keywords' => $this->getMetaKeywords(),
            'meta_title' => $this->getMetaTitle(),
            'images' => $images,
        ];
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Category
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
     * @param int $oldId
     * @return Category
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
     * @param $customTemplate
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * @return Category
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

    //TODO: IS_ATTRIBUTE : Join with attribute
    // - need: is_attribute : javascript form control for selecting item_var_set
    // - need: item_var_set, form_input, is_multi_valued

    /**
     * Set parent_category
     *
     * @param Category $parentCategory
     * @return Category
     */
    public function setParentCategory(Category $parentCategory)
    {
        $this->parent_category = $parentCategory;
        return $this;
    }

    /**
     * Get parent_category
     *
     * @return Category
     */
    public function getParentCategory()
    {
        return $this->parent_category;
    }

    /**
     * @param CategoryProduct $categoryProduct
     * @return Category
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildCategories()
    {
        return $this->child_categories;
    }

    /**
     * @param Category $childCategory
     * @return Category
     */
    public function addChildCategory(Category $childCategory)
    {
        $this->child_categories[] = $childCategory;
        return $this;
    }

    /**
     * @return RecursiveIteratorIterator
     */
    public function getChildren()
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection(array($this));
        $iterator = new RecursiveCategoryIterator($collection);
        return new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * @param ItemVarSet $itemVarSet
     * @return Category
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
     * @param CategoryVarValueDecimal $itemVarValues
     * @return Category
     */
    public function addVarValueDecimal(CategoryVarValueDecimal $itemVarValues)
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
     * @param CategoryVarValueDatetime $itemVarValues
     * @return Category
     */
    public function addVarValueDatetime(CategoryVarValueDatetime $itemVarValues)
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
     * @param CategoryVarValueInt $itemVarValues
     * @return Category
     */
    public function addVarValueInt(CategoryVarValueInt $itemVarValues)
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
     * @param CategoryVarValueText $itemVarValues
     * @return Category
     */
    public function addVarValueText(CategoryVarValueText $itemVarValues)
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
     * @param CategoryVarValueVarchar $itemVarValues
     * @return Category
     */
    public function addVarValueVarchar(CategoryVarValueVarchar $itemVarValues)
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
     * @param CategoryImage $image
     * @return Category
     */
    public function addImage(CategoryImage $image)
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