<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\OrderPayment
 *
 * @ORM\Table(name="order_payment")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\OrderPaymentRepository")
 */
class OrderPayment
    extends AbstractCartEntity
    implements CartEntityInterface
{
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
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    protected $code;

    /**
     * @var string $reference_nbr
     *
     * @ORM\Column(name="reference_nbr", type="string", length=255, nullable=true)
     */
    protected $reference_nbr;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=32, nullable=true)
     */
    protected $status;

    /**
     * @var string $service_account_id
     *
     * @ORM\Column(name="service_account_id", type="string", length=255, nullable=true)
     */
    protected $service_account_id;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    protected $token;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     */
    protected $label;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=4)
     */
    protected $amount;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    protected $currency;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="base_amount", type="decimal", precision=12, scale=4)
     */
    protected $base_amount;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8)
     */
    protected $base_currency;

    /**
     * @var string
     *
     * @ORM\Column(name="confirmation", type="text", nullable=true)
     */
    protected $confirmation;

    /**
     * @var boolean $is_refund
     *
     * @ORM\Column(name="is_refund", type="boolean", nullable=true)
     */
    protected $is_refund;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_authorized", type="boolean", nullable=true)
     */
    protected $is_authorized = false;

    /**
     * @var string $authorization
     *
     * @ORM\Column(name="authorization", type="text", nullable=true)
     */
    protected $authorization;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_captured", type="boolean", nullable=true)
     */
    protected $is_captured = false;

    /**
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $order;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderInvoice
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\OrderInvoice", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_invoice_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $invoice;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::ORDER_PAYMENT;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'code' => $this->getCode(),
            'reference_nbr' => $this->getReferenceNbr(),
            'status' => $this->getStatus(),
            'service_account_id' => $this->getServiceAccountId(),
            'token' => $this->getToken(),
            'label' => $this->getLabel(),
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'base_amount' => $this->getBaseAmount(),
            'base_currency' => $this->getBaseCurrency(),
            'confirmation' => $this->getConfirmation(),
            'is_refund' => $this->getIsRefund(),
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
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $referenceNbr
     * @return $this
     */
    public function setReferenceNbr($referenceNbr)
    {
        $this->reference_nbr = $referenceNbr;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceNbr()
    {
        return $this->reference_nbr;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $serviceAccountId
     * @return $this
     */
    public function setServiceAccountId($serviceAccountId)
    {
        $this->service_account_id = $serviceAccountId;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceAccountId()
    {
        return $this->service_account_id;
    }

    /**
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
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
     * @param $amount
     * @return $this
     */
    public function setBaseAmount($amount)
    {
        $this->base_amount = $amount;
        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->base_amount;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setBaseCurrency($currency)
    {
        $this->base_currency = $currency;
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
     * @param $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation)
    {
        $this->confirmation = $confirmation;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmation()
    {
        return $this->confirmation;
    }

    /**
     * @param $isRefund
     * @return $this
     */
    public function setIsRefund($isRefund)
    {
        $this->is_refund = $isRefund;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRefund()
    {
        return $this->is_refund;
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
     * @param OrderInvoice $invoice
     * @return $this
     */
    public function setInvoice(OrderInvoice $invoice)
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @return OrderInvoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}