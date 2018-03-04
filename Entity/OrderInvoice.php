<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderInvoice
 *
 * @ORM\Table(name="order_invoice")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CommonRepository")
 */
class OrderInvoice
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
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $created_at;

    /**
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="invoices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $order;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderPayment
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderPayment", mappedBy="invoice")
     */
    protected $payments;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    protected $currency;

    /**
     * @var float $amount_due
     *
     * @ORM\Column(name="amount_due", type="decimal", precision=12, scale=4)
     */
    protected $amount_due;

    /**
     * @var float $amount_paid
     *
     * @ORM\Column(name="amount_paid", type="decimal", precision=12, scale=4)
     */
    protected $amount_paid;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8)
     */
    protected $base_currency;

    /**
     * @var float $base_amount_due
     *
     * @ORM\Column(name="base_amount_due", type="decimal", precision=12, scale=4)
     */
    protected $base_amount_due;

    /**
     * @var float $base_amount_paid
     *
     * @ORM\Column(name="base_amount_paid", type="decimal", precision=12, scale=4)
     */
    protected $base_amount_paid;

    /**
     * @var boolean $is_paid
     *
     * @ORM\Column(name="is_paid", type="boolean", nullable=true)
     */
    protected $is_paid;

    public function __construct()
    {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ORDER_INVOICE;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'created_at' => $this->getCreatedAt(),
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
     * @param $amountDue
     * @return $this
     */
    public function setAmountDue($amountDue)
    {
        $this->amount_due = $amountDue;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountDue()
    {
        return $this->amount_due;
    }

    /**
     * @param $amountPaid
     * @return $this
     */
    public function setAmountPaid($amountPaid)
    {
        $this->amount_paid = $amountPaid;
        return $this;
    }

    /**
     * @return float
     */
    public function getAmountPaid()
    {
        return $this->amount_paid;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsPaid($yesNo)
    {
        $this->is_paid = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPaid()
    {
        return $this->is_paid;
    }

    /**
     * Add order payment
     *
     * @param OrderPayment $payment
     * @return Order
     */
    public function addPayment(OrderPayment $payment)
    {
        $this->payments[] = $payment;
        return $this;
    }

    /**
     * Get order payments
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|OrderPayment
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param $baseCurrency
     * @return $this
     */
    public function setBaseCurrency($baseCurrency)
    {
        $this->base_currency = $baseCurrency;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->base_currency;
    }

    /**
     * @param $amountDue
     * @return $this
     */
    public function setBaseAmountDue($amountDue)
    {
        $this->base_amount_due = $amountDue;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseAmountDue()
    {
        return $this->base_amount_due;
    }

    /**
     * @param $amountPaid
     * @return $this
     */
    public function setBaseAmountPaid($amountPaid)
    {
        $this->base_amount_paid = $amountPaid;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseAmountPaid()
    {
        return $this->base_amount_paid;
    }
}
