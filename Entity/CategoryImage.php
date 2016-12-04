<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryImage
 *
 * @ORM\Table(name="category_image")
 * @ORM\Entity
 */
class CategoryImage
    implements CartEntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var boolean $is_default
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     */
    private $is_default;

    /**
     * @var boolean $is_featured
     *
     * @ORM\Column(name="is_featured", type="boolean", nullable=true)
     */
    private $is_featured;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sort_order;

    /**
     * @var string $code an Identifier
     *
     * @ORM\Column(name="code", type="string", length=64)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=16, nullable=true)
     */
    private $size;

    /**
     * @var integer
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    private $width;

    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text", nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="alt_text", type="string", length=255, nullable=true)
     */
    private $alt_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Category", inversedBy="images")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $parent;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CATEGORY_IMAGE;
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
            'is_default' => $this->getIsDefault(),
            'is_featured' => $this->getIsFeatured(),
            'sort_order' => $this->getSortOrder(),
            'code' => $this->getCode(),
            'size' => $this->getSize(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'url' => $this->getUrl(),
            'path' => $this->getPath(),
            'alt_text' => $this->getAltText(),
        ];
    }

    /**
     * Set is_default
     *
     * @param $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault)
    {
        $this->is_default = $isDefault;
        return $this;
    }

    /**
     * Get is_default
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }

    /**
     * Set is_featured
     *
     * @param $isFeatured
     * @return $this
     */
    public function setIsFeatured($isFeatured)
    {
        $this->is_featured = $isFeatured;
        return $this;
    }

    /**
     * Get is_featured
     *
     * @return bool
     */
    public function getIsFeatured()
    {
        return $this->is_featured;
    }

    /**
     * Set sort_order
     *
     * @param integer $sortOrder
     * @return $this
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
     * Set size
     *
     * @param $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
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
     * @return int
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
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param $code
     * @return ItemImage
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return ItemImage
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set alt_text
     *
     * @param string $altText
     * @return $this
     */
    public function setAltText($altText)
    {
        $this->alt_text = $altText;
        return $this;
    }

    /**
     * Get alt_text
     *
     * @return string 
     */
    public function getAltText()
    {
        return $this->alt_text;
    }

    /**
     * Set parent
     *
     * @param \MobileCart\CoreBundle\Entity\Category $parent
     * @return $this;
     */
    public function setParent(Category $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return \MobileCart\CoreBundle\Entity\Category
     */
    public function getParent()
    {
        return $this->parent;
    }
}
