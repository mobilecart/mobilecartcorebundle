<?php

namespace MobileCart\CoreBundle\Entity;

interface CartEntityVarValueInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * @param ItemVar $itemVar
     * @return $this
     */
    public function setItemVar(ItemVar $itemVar);

    /**
     * Get item_var
     *
     * @return \MobileCart\CoreBundle\Entity\ItemVar
     */
    public function getItemVar();

    /**
     * @param $itemVarOption
     * @return $this
     */
    public function setItemVarOption($itemVarOption);

    /**
     * Get item_var_option
     *
     * @return mixed
     */
    public function getItemVarOption();

    /**
     * @param CartEntityEAVInterface $parent
     * @return $this
     */
    public function setParent(CartEntityEAVInterface $parent);

    /**
     * Get parent
     *
     * @return \MobileCart\CoreBundle\Entity\CartEntityEAVInterface
     */
    public function getParent();
}
