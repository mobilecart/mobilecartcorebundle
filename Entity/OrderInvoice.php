<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderInvoice
 *
 * @ORM\Table(name="order_invoice")
 * @ORM\Entity
 */
class OrderInvoice
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
     * @var \MobileCart\CoreBundle\Entity\Order
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Order", inversedBy="invoices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * })
     */
    private $order;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderPayment
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderPayment", mappedBy="invoice")
     */
    private $payments;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    private $currency;

    /**
     * @var float $amount_due
     *
     * @ORM\Column(name="amount_due", type="decimal", precision=12, scale=4)
     */
    private $amount_due;

    /**
     * @var float $amount_paid
     *
     * @ORM\Column(name="amount_paid", type="decimal", precision=12, scale=4)
     */
    private $amount_paid;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8)
     */
    private $base_currency;

    /**
     * @var float $base_amount_due
     *
     * @ORM\Column(name="base_amount_due", type="decimal", precision=12, scale=4)
     */
    private $base_amount_due;

    /**
     * @var float $base_amount_paid
     *
     * @ORM\Column(name="base_amount_paid", type="decimal", precision=12, scale=4)
     */
    private $base_amount_paid;

    /**
     * @var boolean $is_paid
     *
     * @ORM\Column(name="is_paid", type="boolean", nullable=true)
     */
    private $is_paid;

    public function __construct()
    {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
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

        ];
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
