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
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVarSetVar $item_var_set_vars
     *
     * @ORM\OneToMany(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSetVar", mappedBy="item_var_set")
     */
    private $item_var_set_vars;

    /**
     * @var string $code
     *
     * @ORM\Column(name="object_type", type="string", length=32)
     */
    private $object_type;

    public function __construct()
    {
        $this->item_var_set_vars = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ITEM_VAR_SET;
    }

    public function __toString()
    {
        return $this->name;
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
