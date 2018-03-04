<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryProduct
 *
 * @ORM\Table(name="category_product")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CommonRepository")
 */
class CategoryProduct
    extends AbstractCartEntity
    implements CartEntityInterface
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
     * @var \MobileCart\CoreBundle\Entity\Product
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Product", inversedBy="category_products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $product;

    /**
     * @var \MobileCart\CoreBundle\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Category", inversedBy="category_products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $category;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sort_order;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::CATEGORY_PRODUCT;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'sort_order' => $this->getSortOrder(),
        ];
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
