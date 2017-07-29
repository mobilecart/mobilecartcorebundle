<?php

namespace MobileCart\CoreBundle\Entity;

interface CartEntityVarOptionInterface
{
    /**
     * @param $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Get value
     *
     * @return string
     */
    public function getValue();

    /**
     * @param $urlValue
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
     * @param $isInStock
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
     * @param int $sort
     * @return $this
     */
    public function setSortOrder($sort);

    /**
     * @return int
     */
    public function getSortOrder();
}
