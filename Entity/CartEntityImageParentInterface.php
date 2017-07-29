<?php

namespace MobileCart\CoreBundle\Entity;

interface CartEntityImageParentInterface
{
    /**
     * @param CartEntityImageInterface $image
     * @return $this
     */
    public function addImage(CartEntityImageInterface $image);

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages();

    /**
     * @param $code
     * @param bool $isDefault
     * @return string
     */
    public function getImage($code, $isDefault = false);

    /**
     * @param $code
     * @param bool $isDefault
     * @return mixed
     */
    public function getImagePath($code, $isDefault = false);
}
