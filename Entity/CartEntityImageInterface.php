<?php

namespace MobileCart\CoreBundle\Entity;

interface CartEntityImageInterface
{
    /**
     * Set is_default
     *
     * @param $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault);

    /**
     * Get is_default
     *
     * @return bool
     */
    public function getIsDefault();

    /**
     * Set is_featured
     *
     * @param $isFeatured
     * @return $this
     */
    public function setIsFeatured($isFeatured);

    /**
     * Get is_featured
     *
     * @return bool
     */
    public function getIsFeatured();

    /**
     * Set sort_order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * Get sort_order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set size
     *
     * @param $size
     * @return $this
     */
    public function setSize($size);

    /**
     * Get size
     *
     * @return string
     */
    public function getSize();

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width);

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height);

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl();

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getPath();

    /**
     * Set alt_text
     *
     * @param string $altText
     * @return $this
     */
    public function setAltText($altText);

    /**
     * Get alt_text
     *
     * @return string
     */
    public function getAltText();

    /**
     * @param CartEntityImageParentInterface $parent
     * @return $this
     */
    public function setParent(CartEntityImageParentInterface $parent);

    /**
     * @return CartEntityImageParentInterface|null
     */
    public function getParent();
}
