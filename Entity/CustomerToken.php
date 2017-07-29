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
     * @var \MobileCart\CoreBundle\Entity\Customer
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Customer", inversedBy="tokens")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    protected $customer;

    /**
     * @var string $service
     *
     * @ORM\Column(name="service", type="string", length=255, nullable=false)
     */
    protected $service;

    /**
     * @var string $service_account_id
     *
     * @ORM\Column(name="service_account_id", type="string", length=255, nullable=true)
     */
    protected $service_account_id;

    /**
     * @var string $token
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     */
    protected $token;

    /**
     * @var string $cc_type
     *
     * @ORM\Column(name="cc_type", type="string", length=64, nullable=true)
     */
    protected $cc_type;

    /**
     * @var string $cc_last_four
     *
     * @ORM\Column(name="cc_last_four", type="string", length=4, nullable=true)
     */
    protected $cc_last_four;

    /**
     * @var string $cc_fingerprint
     *
     * @ORM\Column(name="cc_fingerprint", type="string", length=255, nullable=true)
     */
    protected $cc_fingerprint;

    /**
     * @var string $created_at
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $created_at;

    /**
     * @var string $crated_at
     *
     * @ORM\Column(name="expires_at", type="datetime", nullable=true)
     */
    protected $expires_at;

    public function __toString()
    {
        return $this->service_account_id; // most common for stripe
    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CUSTOMER_TOKEN;
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
