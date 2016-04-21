<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\CustomerToken
 *
 * @ORM\Table(name="customer_token")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CustomerTokenRepository")
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
     * @var string $service_account_id
     *
     * @ORM\Column(name="service_account_id", type="string", length=255, nullable=true)
     */
    private $service_account_id;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     */
    private $token;

    /**
     * @var string $cc_type
     *
     * @ORM\Column(name="cc_type", type="string", length=64, nullable=true)
     */
    private $cc_type;

    /**
     * @var string $cc_last_four
     *
     * @ORM\Column(name="cc_last_four", type="string", length=4, nullable=true)
     */
    private $cc_last_four;

    /**
     * @var string $cc_fingerprint
     *
     * @ORM\Column(name="cc_fingerprint", type="string", length=255, nullable=true)
     */
    private $cc_fingerprint;

    /**
     * @var string $created_at
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var string $crated_at
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    private $expires_at;

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
            'service_account_id' => $this->getServiceAccountId(),
            'token' => $this->getToken(),
            'cc_type' => $this->getCcType(),
            'cc_last_four' => $this->getCcLastFour(),
            'cc_fingerprint' => $this->getCcFingerprint(),
            'created_at' => $this->getCreatedAt(),
            'expires_at' => $this->getExpiresAt(),
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
     * @param $ccType
     * @return $this
     */
    public function setCcType($ccType)
    {
        $this->cc_type = $ccType;
        return $this;
    }

    /**
     * @return string
     */
    public function getCcType()
    {
        return $this->cc_type;
    }

    /**
     * @param $ccLastFour
     * @return $this
     */
    public function setCcLastFour($ccLastFour)
    {
        $this->cc_last_four = $ccLastFour;
        return $this;
    }

    /**
     * @return string
     */
    public function getCcLastFour()
    {
        return $this->cc_last_four;
    }

    /**
     * @param $ccFingerprint
     * @return $this
     */
    public function setCcFingerprint($ccFingerprint)
    {
        $this->cc_fingerprint = $ccFingerprint;
        return $this;
    }

    /**
     * @return string
     */
    public function getCcFingerprint()
    {
        return $this->cc_fingerprint;
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
     * @param $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expires_at = $expiresAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }

}
