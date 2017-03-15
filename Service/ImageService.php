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

class ImageService
{
    /**
     * @var array
     */
    protected $imageConfigs = [];

    /**
     * @var array
     */
    protected $objectImageType = [];

    /**
     * @var array
     */
    protected $uploadPaths = [];

    /**
     * @param $objectType
     * @param string $code
     * @param int $width
     * @param int $height
     * @param string $defaultPath
     * @return $this
     */
    public function addImageConfig($objectType, $code, $width, $height, $defaultPath = '')
    {
        if (!isset($this->imageConfigs[$objectType])) {
            $this->imageConfigs[$objectType] = [];
        }

        $this->imageConfigs[$objectType][$code] = [
            'width'  => $width,
            'height' => $height,
            'default_path' => $defaultPath,
        ];

        return $this;
    }

    /**
     * @param $objectType
     * @param $relPath
     * @return $this
     */
    public function addImageUploadPath($objectType, $relPath)
    {
        $this->uploadPaths[$objectType] = $relPath;
        return $this;
    }

    /**
     * @param $objectType
     * @return string
     */
    public function getImageUploadPath($objectType)
    {
        return isset($this->uploadPaths[$objectType])
            ? $this->uploadPaths[$objectType]
            : '';
    }

    /**
     * @return array
     */
    public function getImageUploadPaths()
    {
        return $this->uploadPaths;
    }

    /**
     * @param string $objectType
     * @return array
     */
    public function getImageConfigs($objectType)
    {
        return isset($this->imageConfigs[$objectType])
            ? $this->imageConfigs[$objectType]
            : [];
    }

    /**
     * @param string $objectType
     * @param string $code
     * @return array
     */
    public function getImageConfig($objectType, $code)
    {
        return isset($this->imageConfigs[$objectType][$code])
            ? $this->imageConfigs[$objectType][$code]
            : [];
    }

    /**
     * @param $objectType
     * @param $code
     * @return string
     */
    public function getDefaultImage($objectType, $code)
    {
        if ($config = $this->getImageConfig($objectType, $code)) {
            return $config['default_path'];
        }

        return '';
    }
}
