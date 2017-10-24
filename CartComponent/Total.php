<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\CartComponent;

/**
 * Class Total
 * @package MobileCart\CoreBundle\CartComponent
 */
class Total extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{

    const IS_ADD = 'is_add';
    const VALUE = 'value';
    const LABEL = 'label';
    const KEY = 'key';

    public function __construct()
    {
        parent::__construct([
            self::IS_ADD => false,
            self::VALUE  => 0,
            self::LABEL  => '',
            self::KEY    => '',
        ]);
    }

    /**
     * @param bool $isAdd
     * @return $this
     */
    public function setIsAdd($isAdd)
    {
        $this->data[self::IS_ADD] = (bool) $isAdd;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdd()
    {
        return (bool) $this->data[self::IS_ADD];
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->data[self::VALUE] = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->data[self::VALUE];
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->data[self::LABEL] = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->data[self::LABEL];
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->data[self::KEY] = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->data[self::KEY];
    }
}
