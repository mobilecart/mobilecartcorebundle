<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * MobileCart\CoreBundle\Entity\ItemVarSet
 *
 * @ORM\Table(name="item_var_set")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ItemVarSetRepository")
 */
class ItemVarSet
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
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    protected $old_id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSetVar $item_var_set_vars
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSetVar", mappedBy="item_var_set")
     */
    protected $item_var_set_vars;

    /**
     * @var string $code
     *
     * @ORM\Column(name="object_type", type="string", length=32)
     */
    protected $object_type;

    public function __construct()
    {
        $this->item_var_set_vars = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ITEM_VAR_SET;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'old_id' => $this->getOldId(),
            'name' => $this->getName(),
            'object_type' => $this->getObjectType(),
        ];
    }

    /**
     * @param int $oldId
     * @return $this
     */
    public function setOldId($oldId)
    {
        $this->old_id = $oldId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOldId()
    {
        return $this->old_id;
    }

    /**
     * @param $name
     * @return $this
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
     * Add item_var_set_vars
     *
     * @param \MobileCart\CoreBundle\Entity\ItemVarSetVar $itemVarSetVar
     * @return $this
     */
    public function addItemVarSetVar(ItemVarSetVar $itemVarSetVar)
    {
        $this->item_var_set_vars[] = $itemVarSetVar;
        return $this;
    }

    /**
     * Get item_var_set_vars
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItemVarSetVars()
    {
        return $this->item_var_set_vars;
    }

    /**
     * @return array
     */
    public function getItemVars()
    {
        $vars = array();
        $varSetVars = $this->getItemVarSetVars();
        if ($varSetVars) {
            foreach($varSetVars as $varSetVar) {
                $var = $varSetVar->getItemVar();
                $vars[$var->getCode()] = $var;
            }
        }
        return $vars;
    }

    /**
     * @param $objectType
     * @return $this
     */
    public function setObjectType($objectType)
    {
        $this->object_type = $objectType;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->object_type;
    }
}
