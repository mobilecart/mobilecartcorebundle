<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MobileCart\CoreBundle\Entity\ItemVarSetVar
 *
 * @ORM\Table(name="item_var_set_var")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Entity\ItemVarSetVarRepository")
 */
class ItemVarSetVar
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
     * @var \MobileCart\CoreBundle\Entity\ItemVarSet
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVarSet", inversedBy="item_var_set_vars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_set_id", referencedColumnName="id")
     * })
     */
    private $item_var_set;

    /**
     * @var \MobileCart\CoreBundle\Entity\ItemVar
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\ItemVar", inversedBy="item_var_set_vars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="item_var_id", referencedColumnName="id")
     * })
     */
    private $item_var;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sort_order;

    /**
     * @var boolean $is_indexed
     *
     * @ORM\Column(name="is_indexed", type="boolean", nullable=true)
     */
    private $is_indexed;

    /**
     * @var boolean $is_facet
     *
     * @ORM\Column(name="is_facet", type="boolean", nullable=true)
     */
    private $is_facet;

    /**
     * @var boolean $is_sortable
     *
     * @ORM\Column(name="is_sortable", type="boolean", nullable=true)
     */
    private $is_sortable;

    /**
     * @var boolean $is_searchable
     *
     * @ORM\Column(name="is_searchable", type="boolean", nullable=true)
     */
    private $is_searchable;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeName()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::ITEM_VAR_SET_VAR;
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
        return $this->getBaseData();
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
            'sort_order' => $this->getSortOrder(),
            'is_indexed' => $this->getIsIndexed(),
            'is_facet' => $this->getIsFacet(),
            'is_sortable' => $this->getIsSortable(),
            'is_searchable' => $this->getIsSearchable(),
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

    /**
     * Set sort_order
     *
     * @param integer $sortOrder
     * @return CategoryProduct
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;
        return $this;
    }

    /**
     * Get sort_order
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param $isIndexed
     * @return $this
     */
    public function setIsIndexed($isIndexed)
    {
        $this->is_indexed = $isIndexed;
        return $this;
    }

    /**
     * Get is_indexed
     *
     * @return boolean
     */
    public function getIsIndexed()
    {
        return $this->is_indexed;
    }

    /**
     * @param $isFacet
     * @return $this
     */
    public function setIsFacet($isFacet)
    {
        $this->is_facet = $isFacet;
        return $this;
    }

    /**
     * Get is_facet
     *
     * @return boolean
     */
    public function getIsFacet()
    {
        return $this->is_facet;
    }

    /**
     * @param $isSortable
     * @return $this
     */
    public function setIsSortable($isSortable)
    {
        $this->is_sortable = $isSortable;
        return $this;
    }

    /**
     * Get is_sortable
     *
     * @return boolean
     */
    public function getIsSortable()
    {
        return $this->is_sortable;
    }

    /**
     * @param $isSearchable
     * @return $this
     */
    public function setIsSearchable($isSearchable)
    {
        $this->is_searchable = $isSearchable;
        return $this;
    }

    /**
     * Get is_searchable
     *
     * @return boolean
     */
    public function getIsSearchable()
    {
        return $this->is_searchable;
    }
}
