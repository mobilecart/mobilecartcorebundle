<?php

namespace MobileCart\CoreBundle\Entity;


interface ItemVarOptionInterface
{
    /**
     * @return string
     */
    public function getObjectTypeKey();

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return array
     */
    public function getBaseData();

    /**
     * @param int $oldId
     * @return $this
     */
    public function setOldId($oldId);

    /**
     * @return int
     */
    public function getOldId();

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
     * @param string $urlValue
     * @return $this
     */
    public function setUrlValue($urlValue);

    /**
     * @return string
     */
    public function getUrlValue();

    /**
     * @param ItemVar $itemVar
     * @return $this
     */
    public function setItemVar(\MobileCart\CoreBundle\Entity\ItemVar $itemVar);

    /**
     * Get item_var
     *
     * @return \MobileCart\CoreBundle\Entity\ItemVar
     */
    public function getItemVar();

    /**
     * @param bool $isInStock
     * @return $this
     */
    public function setIsInStock($isInStock);

    /**
     * Get is_in_stock
     *
     * @return boolean
     */
    public function getIsInStock();

    /**
     * @param $additionalPrice
     * @return $this
     */
    public function setAdditionalPrice($additionalPrice);

    /**
     * Get additional_price
     *
     * @return float
     */
    public function getAdditionalPrice();

    /**
     * @param $sort
     * @return $this
     */
    public function setSortOrder($sort);

    /**
     * @return int
     */
    public function getSortOrder();
}
