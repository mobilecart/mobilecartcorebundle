<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryProduct
 *
 * @ORM\Table(name="category_product")
 * @ORM\Entity
 */
class CategoryProduct
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
     * @var \MobileCart\CoreBundle\Entity\Product
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Product", inversedBy="category_products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     * })
     */
    private $product;

    /**
     * @var \MobileCart\CoreBundle\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Category", inversedBy="category_products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $category;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sort_order;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeName()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CATEGORY_PRODUCT;
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
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category->getId();
    }
}
