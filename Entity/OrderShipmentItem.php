<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderShipmentItem
 *
 * @ORM\Table(name="order_shipment_item")
 * @ORM\Entity
 */
class OrderShipmentItem
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
     * @var \MobileCart\CoreBundle\Entity\OrderItem
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\OrderItem")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_item_id", referencedColumnName="id")
     * })
     */
    private $order_item;

    /**
     * @var integer
     *
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;

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
     * Set item
     *
     * @param OrderItem $item
     * @return OrderShipmentItem
     */
    public function setOrderItem($item)
    {
        $this->order_item = $item;
        return $this;
    }

    /**
     * Get order item
     *
     * @return OrderItem
     */
    public function getOrderItem()
    {
        return $this->order_item;
    }

    /**
     * Set qty
     *
     * @param integer $qty
     * @return OrderShipmentItem
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
}
