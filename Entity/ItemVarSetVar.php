<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\ItemVarSetVar
 *
 * @ORM\Table(name="item_var_set_var")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ItemVarSetVarRepository")
 */
class ItemVarSetVar extends AbstractCartEntity
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
     * @var \MobileCart\CoreBundle\Entity\ItemVarSet
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSet", inversedBy="item_var_set_vars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_set_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $item_var_set;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVar
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVar", inversedBy="item_var_set_vars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $item_var;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::ITEM_VAR_SET_VAR;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'item_var_set_id' => $this->getItemVarSet()->getId(),
            'item_var_id' => $this->getItemVar()->getId(),
            'item_var_set_name' => $this->getItemVarSet()->getName(),
            'item_var_name' => $this->getItemVar()->getName(),
        ];
    }

    /**
     * Set item_var_set
     *
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
     * @return ItemVarSet
     */
    public function getItemVarSet()
    {
        return $this->item_var_set;
    }

    /**
     * @param ItemVar $itemVar
     * @return $this
     */
    public function setItemVar(ItemVar $itemVar)
    {
        $this->item_var = $itemVar;
        return $this;
    }

    /**
     * Get item_var
     *
     * @return ItemVar
     */
    public function getItemVar()
    {
        return $this->item_var;
    }
}
