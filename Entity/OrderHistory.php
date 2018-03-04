<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderHistory
 *
 * @ORM\Table(name="order_history")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CommonRepository")
 */
class OrderHistory
    extends AbstractCartEntity
    implements CartEntityInterface
{
    const TYPE_GENERAL = 1;
    const TYPE_STATUS = 2;
    const TYPE_PAYMENT = 3;
    const TYPE_SHIPMENT = 4;
    const TYPE_REFUND_PAYMENT = 5;

    /**
     * @var array
     */
    static $historyTypes = [
        self::TYPE_GENERAL => 'General Comment',
        self::TYPE_STATUS => 'Order Status Update',
        self::TYPE_PAYMENT => 'Payment',
        self::TYPE_SHIPMENT => 'Shipment',
        self::TYPE_REFUND_PAYMENT => 'Refund Payment',
    ];

    /**
     * @var integer
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
     * @var string
     *
     * @ORM\Column(name="user", type="string", length=255)
     */
    protected $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="history_type", type="integer")
     */
    protected $history_type;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    /**
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="history")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $order;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::ORDER_HISTORY;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'history_type' => $this->getHistoryType(),
            'message' => $this->getMessage(),
        ];
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
     * Set user
     *
     * @param string $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set history_type
     *
     * @param integer $historyType
     * @return $this
     */
    public function setHistoryType($historyType)
    {
        $this->history_type = $historyType;
        return $this;
    }

    /**
     * Get history_type
     *
     * @return integer 
     */
    public function getHistoryType()
    {
        return $this->history_type;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get order
     *
     * @return \MobileCart\CoreBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
