<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WebhookLog
 *
 * @ORM\Table(name="webhook_log")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\WebhookLogRepository")
 */
class WebhookLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="source_ip", type="string", length=128, nullable=true)
     */
    private $source_ip;

    /**
     * @var string
     *
     * @ORM\Column(name="request_body", type="text")
     */
    private $request_body;

    /**
     * @var string
     *
     * @ORM\Column(name="request_method", type="string", length=8, nullable=true)
     */
    private $request_method;

    /**
     * @var string
     *
     * @ORM\Column(name="service", type="string", length=64, nullable=true)
     */
    private $service;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_processed", type="boolean", nullable=true)
     */
    private $is_processed;


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
     * Set source_ip
     *
     * @param string $source_ip
     * @return WebhookLog
     */
    public function setSourceIp($source_ip)
    {
        $this->source_ip = $source_ip;

        return $this;
    }

    /**
     * Get source_ip
     *
     * @return string 
     */
    public function getSourceIp()
    {
        return $this->source_ip;
    }

    /**
     * Set request_body
     *
     * @param string $request_body
     * @return WebhookLog
     */
    public function setRequestBody($request_body)
    {
        $this->request_body = $request_body;

        return $this;
    }

    /**
     * Get request_body
     *
     * @return string 
     */
    public function getRequestBody()
    {
        return $this->request_body;
    }

    /**
     * Set request_method
     *
     * @param string $request_method
     * @return WebhookLog
     */
    public function setRequestMethod($request_method)
    {
        $this->request_method = $request_method;

        return $this;
    }

    /**
     * Get request_method
     *
     * @return string
     */
    public function getRequestMethod()
    {
        return $this->request_method;
    }

    /**
     * Set service
     *
     * @param string $service
     * @return WebhookLog
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string 
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $created_at
     * @return WebhookLog
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param \DateTime $updated_at
     * @return WebhookLog
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set is_processed
     *
     * @param boolean $is_processed
     * @return WebhookLog
     */
    public function setIsProcessed($is_processed)
    {
        $this->is_processed = $is_processed;

        return $this;
    }

    /**
     * Get is_processed
     *
     * @return boolean 
     */
    public function getIsProcessed()
    {
        return $this->is_processed;
    }
}
