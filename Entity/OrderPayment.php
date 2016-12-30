<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\OrderPayment
 *
 * @ORM\Table(name="order_payment")
 * @ORM\Entity
 */
class OrderPayment
    implements CartEntityInterface
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
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string $reference_nbr
     *
     * @ORM\Column(name="reference_nbr", type="string", length=255, nullable=true)
     */
    private $reference_nbr;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=32, nullable=true)
     */
    private $status;

    /**
     * @var string $service_account_id
     *
     * @ORM\Column(name="service_account_id", type="string", length=255, nullable=true)
     */
    private $service_account_id;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="amount", type="decimal", precision=12, scale=4)
     */
    private $amount;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    private $currency;

    /**
     * @var float $amount
     *
     * @ORM\Column(name="base_amount", type="decimal", precision=12, scale=4)
     */
    private $base_amount;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8)
     */
    private $base_currency;

    /**
     * @var string
     *
     * @ORM\Column(name="confirmation", type="text", nullable=true)
     */
    private $confirmation;

    /**
     * @var boolean $is_refund
     *
     * @ORM\Column(name="is_refund", type="boolean", nullable=true)
     */
    private $is_refund;

    /**
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * })
     */
    private $order;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderInvoice
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\OrderInvoice", inversedBy="payments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_invoice_id", referencedColumnName="id")
     * })
     */
    private $invoice;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::ORDER_PAYMENT;
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
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * Set amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     * Set base_amount
     *
     * @param float $amount
     */
    public function setBaseAmount($amount)
    {
        $this->base_amount = $amount;
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
     * Set order
     *
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
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