<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cart
 *
 * This class is mainly used for storing json, and the customer_id .
 * It is also used for storing wishlists .
 *
 * Note : some stores might only need session storage,
 *  and might not need to use this table for frontend traffic
 *
 *
 * @ORM\Table(name="cart")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CartRepository")
 */
class Cart
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
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $customer;

    /**
     * @var \MobileCart\CoreBundle\Entity\CartItem
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\CartItem", mappedBy="cart")
     */
    private $cart_items;

    /**
     * @var text $json
     *
     * @ORM\Column(name="json", type="text")
     */
    private $json;

    /**
     * @var boolean $is_wishlist
     *
     * @ORM\Column(name="is_wishlist", type="boolean", nullable=true)
     */
    private $is_wishlist;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    public function __construct()
    {
        $this->cart_items = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CART;
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
        return $this->getBaseData();
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'json' => $this->getJson(),
            'is_wishlist' => $this->getIsWishlist(),
            'created_at' => $this->getCreatedAt(),
        ];
    }

    /**
     * Set json
     *
     * @param string $json
     */
    public function setJson($json)
    {
        $this->json = $json;
        return $this;
    }

    /**
     * Get json
     *
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set is_wishlist
     *
     * @param boolean $isWishlist
     */
    public function setIsWishlist($isWishlist)
    {
        $this->is_wishlist = $isWishlist;
        return $this;
    }

    /**
     * Get is_wishlist
     *
     * @return boolean 
     */
    public function getIsWishlist()
    {
        return $this->is_wishlist;
    }

    /**
     * Set customer
     *
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get customer
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param CartItem $cartItem
     * @return $this
     */
    public function addCartItem(CartItem $cartItem)
    {
        $this->cart_items[] = $cartItem;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|CartItem
     */
    public function getCartItems()
    {
        return $this->cart_items;
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
}