<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerGroup
 *
 * @ORM\Table(name="customer_group")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\CustomerGroupRepository")
 */
class CustomerGroup
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * @var \MobileCart\CoreBundle\Entity\Customer $customers
     *
     * @ORM\ManyToMany(targetEntity="MobileCart\CoreBundle\Entity\Customer", inversedBy="groups")
     * @ORM\JoinTable(name="customer_group_member",
     *   joinColumns={
     *     @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $customers;

    public function __construct()
    {
        $this->customers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CUSTOMER_GROUP;
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
            'name' => $this->getName(),
        ];
    }

    /**
     * Set name
     *
     * @param string $name
     * @return CustomerGroup
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set position
     *
     * @param integer $position
     * @return CustomerGroup
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Add customer
     *
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer)
    {
        $this->customers[] = $customer;
    }

    /**
     * Get customers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomers()
    {
        return $this->customers;
    }
}
