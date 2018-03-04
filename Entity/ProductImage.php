<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductImage
 *
 * @ORM\Table(name="product_image")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CommonRepository")
 */
class ProductImage
    extends AbstractCartEntity
    implements CartEntityInterface, CartEntityImageInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var boolean $is_default
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     */
    protected $is_default;

    /**
     * @var boolean $is_featured
     *
     * @ORM\Column(name="is_featured", type="boolean", nullable=true)
     */
    protected $is_featured;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sort_order;

    /**
     * @var string $code an Identifier
     *
     * @ORM\Column(name="code", type="string", length=64)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=16, nullable=true)
     */
    protected $size;

    /**
     * @var integer
     *
     * @ORM\Column(name="width", type="integer", nullable=true)
     */
    protected $width;

    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="integer", nullable=true)
     */
    protected $height;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text", nullable=true)
     */
    protected $path;

    /**
     * @var string
     *
     * @ORM\Column(name="alt_text", type="string", length=255, nullable=true)
     */
    protected $alt_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\Product
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Product", inversedBy="images")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $parent;

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
     * Set id
     *
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
        return \MobileCart\CoreBundle\Constants\EntityConstants::PRODUCT_IMAGE;
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
     * @return $this
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
     * @return $this
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
     * @param CartEntityImageParentInterface $parent
     * @return $this
     */
    public function setParent(CartEntityImageParentInterface $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return CartEntityImageParentInterface
     */
    public function getParent()
    {
        return $this->parent;
    }
}
