<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjectLog
 *
 * @ORM\Table(name="object_log")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ObjectLogRepository")
 */
class ObjectLog
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
     * @ORM\Column(name="object_type", type="string", length=32)
     */
    private $object_type;

    /**
     * @var int
     *
     * @ORM\Column(name="object_id", type="integer")
     */
    private $object_id;

    /**
     * @var string
     *
     * @ORM\Column(name="object_data", type="text", nullable=true)
     */
    private $object_data;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=16, nullable=true)
     */
    private $action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;


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
     * Set object_type
     *
     * @param string $object_type
     * @return ObjectLog
     */
    public function setObjectType($object_type)
    {
        $this->object_type = $object_type;
        return $this;
    }

    /**
     * Get object_type
     *
     * @return string 
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * Set object_id
     *
     * @param integer $object_id
     * @return ObjectLog
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;

        return $this;
    }

    /**
     * Get object_id
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Set object_data
     *
     * @param string $object_data
     * @return ObjectLog
     */
    public function setObjectData($object_data)
    {
        $this->object_data = $object_data;
        return $this;
    }

    /**
     * Get object_data
     *
     * @return string 
     */
    public function getObjectData()
    {
        return $this->object_data;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ObjectLog
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return ObjectLog
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $created_at
     * @return ObjectLog
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
}
