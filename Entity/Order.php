<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * MobileCart\CoreBundle\Entity\Order
 *
 * @ORM\Table(name="order_sale", indexes={@ORM\Index(name="order_email_idx", columns={"email"})})
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\OrderRepository")
 */
class Order
    implements CartEntityInterface, CartEntityEAVInterface
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
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     *
     */
    private $status;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     *
     */
    private $email;

    /**
     * @var string $billing_name
     *
     * @ORM\Column(name="billing_name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"billing_address"})
     */
    private $billing_name;

    /**
     * @var string $billing_company
     *
     * @ORM\Column(name="billing_company", type="string", length=255, nullable=true)
     */
    private $billing_company;

    /**
     * @var string $billing_phone
     *
     * @ORM\Column(name="billing_phone", type="string", length=24, nullable=true)
     */
    private $billing_phone;

    /**
     * @var string $billing_street
     *
     * @ORM\Column(name="billing_street", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"billing_address"})
     */
    private $billing_street;

    /**
     * @var string $billing_street2
     *
     * @ORM\Column(name="billing_street2", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"billing_address"})
     */
    private $billing_street2;

    /**
     * @var string $billing_city
     *
     * @ORM\Column(name="billing_city", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"billing_address"})
     */
    private $billing_city;

    /**
     * @var string $billing_region
     *
     * @ORM\Column(name="billing_region", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={"billing_address"})
     */
    private $billing_region;

    /**
     * @var string $billing_postcode
     *
     * @ORM\Column(name="billing_postcode", type="string", length=32, nullable=true)
     * @Assert\NotBlank(groups={"billing_address"})
     */
    private $billing_postcode;

    /**
     * @var string $billing_country_id
     *
     * @ORM\Column(name="billing_country_id", type="string", length=2, nullable=true)
     * @Assert\NotBlank(groups={"billing_address"})
     */
    private $billing_country_id;

    // is_fraud
    // fraud_rating
    // fraud_info

    /**
     * @var string $currency
     *
     * @ORM\Column(name="reference_nbr", type="string", length=16)
     */
    private $reference_nbr;

    /**
     * @var string $payment_authorize
     *
     * @ORM\Column(name="payment_authorize", type="text", nullable=true)
     */
    private $payment_authorize;

    /**
     * @var string $json
     *
     * @ORM\Column(name="json", type="text")
     */
    private $json;

    /**
     * @var string $currency
     *
     * @ORM\Column(name="currency", type="string", length=8)
     */
    private $currency;

    /**
     * @var float $total
     *
     * @ORM\Column(name="total", type="decimal", precision=12, scale=4)
     */
    private $total;

    /**
     * @var float $item_total
     *
     * @ORM\Column(name="item_total", type="decimal", precision=12, scale=4)
     */
    private $item_total;

    /**
     * @var float $tax_total
     *
     * @ORM\Column(name="tax_total", type="decimal", precision=12, scale=4)
     */
    private $tax_total;

    /**
     * @var float $discount_total
     *
     * @ORM\Column(name="discount_total", type="decimal", precision=12, scale=4)
     */
    private $discount_total;

    /**
     * @var float $shipping_total
     *
     * @ORM\Column(name="shipping_total", type="decimal", precision=12, scale=4)
     */
    private $shipping_total;

    /**
     * @var float $refund_total
     *
     * @ORM\Column(name="refund_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $refund_total;

    /**
     * @var string $base_currency
     *
     * @ORM\Column(name="base_currency", type="string", length=8)
     */
    private $base_currency;

    /**
     * @var float $base_total
     *
     * @ORM\Column(name="base_total", type="decimal", precision=12, scale=4)
     */
    private $base_total;

    /**
     * @var float $base_item_total
     *
     * @ORM\Column(name="base_item_total", type="decimal", precision=12, scale=4)
     */
    private $base_item_total;

    /**
     * @var float $base_tax_total
     *
     * @ORM\Column(name="base_tax_total", type="decimal", precision=12, scale=4)
     */
    private $base_tax_total;

    /**
     * @var float $base_discount_total
     *
     * @ORM\Column(name="base_discount_total", type="decimal", precision=12, scale=4)
     */
    private $base_discount_total;

    /**
     * @var float $base_shipping_total
     *
     * @ORM\Column(name="base_shipping_total", type="decimal", precision=12, scale=4)
     */
    private $base_shipping_total;

    /**
     * @var float $base_refund_total
     *
     * @ORM\Column(name="base_refund_total", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $base_refund_total;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderItem
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderItem", mappedBy="order")
     */
    private $items;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderInvoice
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderInvoice", mappedBy="order")
     */
    private $invoices;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderRefund
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderRefund", mappedBy="order")
     */
    private $refunds;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderPayment
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderPayment", mappedBy="order")
     */
    private $payments;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderShipment
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderShipment", mappedBy="order")
     */
    private $shipments;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderHistory
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderHistory", mappedBy="order")
     */
    private $history;

    /**
     * @var \MobileCart\CoreBundle\Entity\Customer
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSet
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_set_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $item_var_set;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderVarValueDatetime
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderVarValueDatetime", mappedBy="parent")
     */
    private $var_values_datetime;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderVarValueDecimal
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderVarValueDecimal", mappedBy="parent")
     */
    private $var_values_decimal;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderVarValueInt
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderVarValueInt", mappedBy="parent")
     */
    private $var_values_int;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderVarValueText
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderVarValueText", mappedBy="parent")
     */
    private $var_values_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\OrderVarValueVarchar
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\OrderVarValueVarchar", mappedBy="parent")
     */
    private $var_values_varchar;

    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->shipments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->invoices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->refunds = new \Doctrine\Common\Collections\ArrayCollection();
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->history = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = 'processing';
    }

    public function __toString()
    {
        return $this->getReferenceNbr();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ORDER;
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
     * Get All Data or specific key of data
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        if (strlen($key) > 0) {

            $data = $this->getBaseData();
            if (isset($data[$key])) {
                return $data[$key];
            }

            $data = $this->getVarValuesData();
            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return array_merge($this->getVarValuesData(), $this->getBaseData());
    }

    /**
     * @return array
     */
    public function getLuceneVarValuesData()
    {
        // Note:
        // be careful with adding foreign relationships here
        // since it will add 1 query every time an item is loaded

        $pData = $this->getBaseData();

        $varValues = $this->getVarValues();
        if (!$varValues->count()) {
            return $pData;
        }

        foreach($varValues as $itemVarValue) {

            /** @var ItemVar $itemVar */
            $itemVar = $itemVarValue->getItemVar();

            $value = $itemVarValue->getValue();
            switch($itemVar->getDatatype()) {
                case 'int':
                    $value = (int) $value;
                    break;
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'datetime':
                    // for Lucene
                    $value = gmdate('Y-m-d\TH:i:s\Z', strtotime($value));
                    break;
                default:
                    $value = (string) $value;
                    break;
            }

            if ($itemVar->getFormInput() == 'multiselect') {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = array();
                }
                $data[$itemVar->getCode()][] = $value;
            } else {
                $data[$itemVar->getCode()] = $value;
            }

        }

        return array_merge($this->getVarValuesData(), $pData);
    }

    /**
     * Get Var Values as associative Array
     *
     * @return array
     */
    public function getVarValuesData()
    {
        $varSet = $this->getItemVarSet();
        $varSetId = ($varSet instanceof ItemVarSet)
            ? $varSet->getId()
            : null;

        $data = $this->getBaseData();
        $data['var_set_id'] = $varSetId;
        //$data['tags'] = $this->getTagsData();

        $varValues = $this->getVarValues();
        if (!$varValues) {
            return $data;
        }

        foreach($varValues as $itemVarValue) {

            /** @var ItemVar $itemVar */
            $itemVar = $itemVarValue->getItemVar();

            $value = $itemVarValue->getValue();
            switch($itemVar->getDatatype()) {
                case 'int':
                    $value = (int) $value;
                    break;
                case 'decimal':
                    $value = (float) $value;
                    break;
                case 'datetime':
                    $value = gmdate('Y-m-d H:i:s', strtotime($value));
                    break;
                default:
                    $value = (string) $value;
                    break;
            }

            if ($itemVar->getFormInput() == 'multiselect') {
                if (!isset($data[$itemVar->getCode()])) {
                    $data[$itemVar->getCode()] = array();
                }
                $data[$itemVar->getCode()][] = $value;
            } else {
                $data[$itemVar->getCode()] = $value;
            }

        }

        return $data;
    }

    /**
     *
     * @return array
     */
    public function getVarValues()
    {
        $values = new ArrayCollection();
        $datetimes = $this->getVarValuesDatetime();
        $decimals = $this->getVarValuesDecimal();
        $ints = $this->getVarValuesInt();
        $texts = $this->getVarValuesText();
        $varchars = $this->getVarValuesVarchar();

        if ($datetimes) {
            foreach($datetimes as $value) {
                $values->add($value);
            }
        }

        if ($decimals) {
            foreach($decimals as $value) {
                $values->add($value);
            }
        }

        if ($ints) {
            foreach($ints as $value) {
                $values->add($value);
            }
        }

        if ($texts) {
            foreach($texts as $value) {
                $values->add($value);
            }
        }

        if ($varchars) {
            foreach($varchars as $value) {
                $values->add($value);
            }
        }

        return $values;
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
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'created_at' => $this->getCreatedAt(),
            'status' => $this->getStatus(),
            'reference_nbr' => $this->getReferenceNbr(),
            'payment_authorize' => $this->getPaymentAuthorize(),
            'customer_id' => $this->getCustomer() ? $this->getCustomer()->getId() : 0,
            'customer_name' => $this->getCustomer() ? $this->getCustomer()->getName() : '',
            'email' => $this->getEmail(),
            'currency' => $this->getCurrency(),
            'total' => $this->getTotal(),
            'item_total' => $this->getItemTotal(),
            'tax_total' => $this->getTaxTotal(),
            'discount_total' => $this->getDiscountTotal(),
            'shipping_total' => $this->getShippingTotal(),
            'refund_total' => $this->getRefundTotal(),
            'base_currency' => $this->getBaseCurrency(),
            'base_total' => $this->getBaseTotal(),
            'base_item_total' => $this->getBaseItemTotal(),
            'base_tax_total' => $this->getBaseTaxTotal(),
            'base_discount_total' => $this->getBaseDiscountTotal(),
            'base_shipping_total' => $this->getBaseShippingTotal(),
            'base_refund_total' => $this->getBaseRefundTotal(),
            'billing_name' => $this->getBillingName(),
            'billing_company' => $this->getBillingCompany(),
            'billing_phone' => $this->getBillingPhone(),
            'billing_street' => $this->getBillingStreet(),
            'billing_street2' => $this->getBillingStreet2(),
            'billing_city' => $this->getBillingCity(),
            'billing_region' => $this->getBillingRegion(),
            'billing_postcode' => $this->getBillingPostcode(),
            'billing_country_id' => $this->getBillingCountryId(),
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
     * @param $paymentAuthorize
     * @return $this
     */
    public function setPaymentAuthorize($paymentAuthorize)
    {
        $this->payment_authorize = $paymentAuthorize;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentAuthorize()
    {
        return $this->payment_authorize;
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
     * @param $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return float 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param $itemTotal
     * @return $this
     */
    public function setItemTotal($itemTotal)
    {
        $this->item_total = $itemTotal;
        return $this;
    }

    /**
     * Get item_total
     *
     * @return float 
     */
    public function getItemTotal()
    {
        return $this->item_total;
    }

    /**
     * @param $taxTotal
     * @return $this
     */
    public function setTaxTotal($taxTotal)
    {
        $this->tax_total = $taxTotal;
        return $this;
    }

    /**
     * Get tax_total
     *
     * @return float 
     */
    public function getTaxTotal()
    {
        return $this->tax_total;
    }

    /**
     * @param $discountTotal
     * @return $this
     */
    public function setDiscountTotal($discountTotal)
    {
        $this->discount_total = $discountTotal;
        return $this;
    }

    /**
     * Get discount_total
     *
     * @return float 
     */
    public function getDiscountTotal()
    {
        return $this->discount_total;
    }

    /**
     * @param $shippingTotal
     * @return $this
     */
    public function setShippingTotal($shippingTotal)
    {
        $this->shipping_total = $shippingTotal;
        return $this;
    }

    /**
     * Get shipping_total
     *
     * @return float 
     */
    public function getShippingTotal()
    {
        return $this->shipping_total;
    }

    /**
     * @param $refundTotal
     * @return $this
     */
    public function setRefundTotal($refundTotal)
    {
        $this->refund_total = $refundTotal;
        return $this;
    }

    /**
     * @return float
     */
    public function getRefundTotal()
    {
        return $this->refund_total;
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
     * @param $total
     * @return $this
     */
    public function setBaseTotal($total)
    {
        $this->base_total = $total;
        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getBaseTotal()
    {
        return $this->base_total;
    }

    /**
     * @param $itemTotal
     * @return $this
     */
    public function setBaseItemTotal($itemTotal)
    {
        $this->base_item_total = $itemTotal;
        return $this;
    }

    /**
     * Get item_total
     *
     * @return float
     */
    public function getBaseItemTotal()
    {
        return $this->base_item_total;
    }

    /**
     * @param $taxTotal
     * @return $this
     */
    public function setBaseTaxTotal($taxTotal)
    {
        $this->base_tax_total = $taxTotal;
        return $this;
    }

    /**
     * Get tax_total
     *
     * @return float
     */
    public function getBaseTaxTotal()
    {
        return $this->base_tax_total;
    }

    /**
     * @param $discountTotal
     * @return $this
     */
    public function setBaseDiscountTotal($discountTotal)
    {
        $this->base_discount_total = $discountTotal;
        return $this;
    }

    /**
     * Get discount_total
     *
     * @return float
     */
    public function getBaseDiscountTotal()
    {
        return $this->base_discount_total;
    }

    /**
     * @param $shippingTotal
     * @return $this
     */
    public function setBaseShippingTotal($shippingTotal)
    {
        $this->base_shipping_total = $shippingTotal;
        return $this;
    }

    /**
     * Get shipping_total
     *
     * @return float
     */
    public function getBaseShippingTotal()
    {
        return $this->base_shipping_total;
    }

    /**
     * @param $refundTotal
     * @return $this
     */
    public function setBaseRefundTotal($refundTotal)
    {
        $this->base_refund_total = $refundTotal;
        return $this;
    }

    /**
     * @return float
     */
    public function getBaseRefundTotal()
    {
        return $this->base_refund_total;
    }

    /**
     * Add order items
     *
     * @param OrderItem $item
     * @return Order
     */
    public function addItem(OrderItem $item)
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Get order items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add order shipment
     *
     * @param OrderShipment $shipment
     * @return Order
     */
    public function addShipment(OrderShipment $shipment)
    {
        $this->shipments[] = $shipment;
        return $this;
    }

    /**
     * Get order shipments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShipments()
    {
        return $this->shipments;
    }

    /**
     * @param OrderInvoice $invoice
     * @return $this
     */
    public function addInvoice(OrderInvoice $invoice)
    {
        $this->invoices[] = $invoice;
        return $this;
    }

    /**
     * @return ArrayCollection|OrderInvoice
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * @param OrderRefund $refund
     * @return $this
     */
    public function addRefund(OrderRefund $refund)
    {
        $this->refunds[] = $refund;
        return $this;
    }

    /**
     * @return ArrayCollection|OrderRefund
     */
    public function getRefunds()
    {
        return $this->refunds;
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
     * Add order history
     *
     * @param OrderHistory $history
     * @return OrderHistory
     */
    public function addHistory(OrderHistory $history)
    {
        $this->history[] = $history;
        return $this;
    }

    /**
     * Get order history
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|OrderHistory
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setBillingName($name)
    {
        $this->billing_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingName()
    {
        return $this->billing_name;
    }

    /**
     * @param $company
     * @return $this
     */
    public function setBillingCompany($company)
    {
        $this->billing_company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingCompany()
    {
        return $this->billing_company;
    }

    /**
     * @param $billingPhone
     * @return $this
     */
    public function setBillingPhone($billingPhone)
    {
        $this->billing_phone = $billingPhone;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingPhone()
    {
        return $this->billing_phone;
    }

    /**
     * @param $billingStreet
     * @return $this
     */
    public function setBillingStreet($billingStreet)
    {
        $this->billing_street = $billingStreet;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingStreet()
    {
        return $this->billing_street;
    }

    /**
     * @param $billingStreet2
     * @return $this
     */
    public function setBillingStreet2($billingStreet2)
    {
        $this->billing_street2 = $billingStreet2;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingStreet2()
    {
        return $this->billing_street2;
    }

    /**
     * @param $billingCity
     * @return $this
     */
    public function setBillingCity($billingCity)
    {
        $this->billing_city = $billingCity;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingCity()
    {
        return $this->billing_city;
    }

    /**
     * @param $billingRegion
     * @return $this
     */
    public function setBillingRegion($billingRegion)
    {
        $this->billing_region = $billingRegion;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingRegion()
    {
        return $this->billing_region;
    }

    /**
     * @param $billingPostcode
     * @return $this
     */
    public function setBillingPostcode($billingPostcode)
    {
        $this->billing_postcode = $billingPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingPostcode()
    {
        return $this->billing_postcode;
    }

    /**
     * @param $billingCountryId
     * @return $this
     */
    public function setBillingCountryId($billingCountryId)
    {
        $this->billing_country_id = $billingCountryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingCountryId()
    {
        return $this->billing_country_id;
    }

    /**
     * @param $json
     * @return $this
     */
    public function setJson($json)
    {
        $this->json = $json;
        return $this;
    }

    /**
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * @param ItemVarSet $itemVarSet
     * @return $this
     */
    public function setItemVarSet(ItemVarSet $itemVarSet)
    {
        $this->item_var_set = $itemVarSet;
        return $this;
    }

    /**
     * Get item_var_set
     *
     * @return \MobileCart\CoreBundle\Entity\ItemVarSet
     */
    public function getItemVarSet()
    {
        return $this->item_var_set;
    }

    /**
     * @param OrderVarValueDecimal $itemVarValues
     * @return $this
     */
    public function addVarValueDecimal(OrderVarValueDecimal $itemVarValues)
    {
        $this->var_values_decimal[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_decimal
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDecimal()
    {
        return $this->var_values_decimal;
    }

    /**
     * @param OrderVarValueDatetime $itemVarValues
     * @return $this
     */
    public function addVarValueDatetime(OrderVarValueDatetime $itemVarValues)
    {
        $this->var_values_datetime[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_datetime
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesDatetime()
    {
        return $this->var_values_datetime;
    }

    /**
     * @param OrderVarValueInt $itemVarValues
     * @return $this
     */
    public function addVarValueInt(OrderVarValueInt $itemVarValues)
    {
        $this->var_values_int[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesInt()
    {
        return $this->var_values_int;
    }

    /**
     * @param OrderVarValueText $itemVarValues
     * @return $this
     */
    public function addVarValueText(OrderVarValueText $itemVarValues)
    {
        $this->var_values_text[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_text
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesText()
    {
        return $this->var_values_text;
    }

    /**
     * @param OrderVarValueVarchar $itemVarValues
     * @return $this
     */
    public function addVarValueVarchar(OrderVarValueVarchar $itemVarValues)
    {
        $this->var_values_varchar[] = $itemVarValues;
        return $this;
    }

    /**
     * Get var_values_varchar
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVarValuesVarchar()
    {
        return $this->var_values_varchar;
    }
}
