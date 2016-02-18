<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\Constants\EntityConstants;

class ImageService
{
    /**
     * @var array
     */
    protected $imageSizes = [];

    /**
     * @var mixed
     */
    protected $entityService;

    /**
     * @param $objectType
     * @param string $code
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function addImageSize($objectType, $code, $width, $height)
    {
        if (!isset($this->imageSizes[$objectType])) {
            $this->imageSizes[$objectType] = [];
        }

        $this->imageSizes[$objectType][$code] = [
            'width'  => $width,
            'height' => $height,
        ];

        return $this;
    }

    /**
     * @param string $objectType
     * @return array
     */
    public function getImageSizes($objectType)
    {
        return isset($this->imageSizes[$objectType])
            ? $this->imageSizes[$objectType]
            : [];
    }

    /**
     * @param string $objectType
     * @param string $code
     * @return array
     */
    public function getImageSize($objectType, $code)
    {
        return isset($this->imageSizes[$objectType][$code])
            ? $this->imageSizes[$objectType][$code]
            : [];
    }

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param string $objectType Image Object Key
     * @param $parentEntity object|int Entity or Entity ID
     * @param array $images
     * @return bool
     */
    public function updateImages($objectType, $parentEntity, array $images)
    {
        return $this->getEntityService()->updateImages($objectType, $parentEntity, $images);
    }
}
