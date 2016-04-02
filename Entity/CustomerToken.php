<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\CustomerToken
 *
 * @ORM\Table(name="customer_token")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Entity\CustomerTokenRepository")
 */
class CustomerToken
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
     * @var \MobileCart\CoreBundle\Entity\Customer
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Customer", inversedBy="tokens")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $customer;

    /**
     * @var string $service
     *
     * @ORM\Column(name="service", type="string", length=255, nullable=false)
     */
    private $service;

    /**
     * @var string $crated_at
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var string $last_payment_at
     *
     * @ORM\Column(name="last_payment_at", type="datetime", nullable=true)
     */
    private $last_payment_at;

    /**
     * @var string $next_payment_at
     *
     * @ORM\Column(name="next_payment_at", type="datetime", nullable=true)
     */
    private $next_payment_at;

    /**
     * @var float $last_payment_amount
     *
     * @ORM\Column(name="last_payment_amount", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $last_payment_amount;

    /**
     * @var float $next_payment_amount
     *
     * @ORM\Column(name="next_payment_amount", type="decimal", precision=12, scale=4, nullable=true)
     */
    private $next_payment_amount;

    public function __toString()
    {
        return $this->service;
    }

    public function getObjectTypeName()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CUSTOMER_TOKEN;
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
            'id' => $this->getId(),
            'customer_id' => $this->getCustomer()->getId(),
            'service' => $this->getService(),
            'created_at' => $this->getCreatedAt(),
        ];
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
     * @param $service
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
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
     * Set lastPaymentAt
     *
     * @param \DateTime $lastPaymentAt
     * @return $this
     */
    public function setLastPaymentAt($lastPaymentAt)
    {
        $this->last_payment_at = $lastPaymentAt;
        return $this;
    }

    /**
     * Get lastPaymentAt
     *
     * @return \DateTime
     */
    public function getLastPaymentAt()
    {
        return $this->last_payment_at;
    }

    /**
     * Set nextPaymentAt
     *
     * @param \DateTime $nextPaymentAt
     * @return $this
     */
    public function setNextPaymentAt($nextPaymentAt)
    {
        $this->next_payment_at = $nextPaymentAt;
        return $this;
    }

    /**
     * Get nextPaymentAt
     *
     * @return \DateTime
     */
    public function getNextPaymentAt()
    {
        return $this->next_payment_at;
    }

    /**
     * Set lastPaymentAamount
     *
     * @param $lastPaymentAmount
     * @return $this
     */
    public function setLastPaymentAmount($lastPaymentAmount)
    {
        $this->last_payment_amount = $lastPaymentAmount;
        return $this;
    }

    /**
     * Get lastPaymentAmount
     *
     * @return mixed
     */
    public function getLastPaymentAmount()
    {
        return $this->last_payment_amount;
    }

    /**
     * Set nextPaymentAmount
     *
     * @param $nextPaymentAmount
     * @return $this
     */
    public function setNextPaymentAmount($nextPaymentAmount)
    {
        $this->next_payment_amount = $nextPaymentAmount;
        return $this;
    }

    /**
     * Get nextPaymentAmount
     *
     * @return mixed
     */
    public function getNextPaymentAmount()
    {
        return $this->next_payment_amount;
    }
}
