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
 * Class RuleCondition
 * @package MobileCart\CoreBundle\CartComponent
 */
class RuleCondition extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    const NAME = 'name';
    const OPERATOR = 'operator';
    const IS_NOT = 'is_not';
    const IS_SLOT = 'is_slot';
    const SOURCE_VALUE = 'source_value';
    const COMPARE_VALUE = 'compare_value';
    const ENTITY_TYPE = 'entity_type';
    const ENTITY_FIELD = 'entity_field';
    const ENTITY_TYPE_FIELD = 'entity_type_field';

    const COMPARE_GREATER_THAN = 'gt';
    const COMPARE_LESS_THAN = 'lt';
    const COMPARE_GREATER_THAN_EQUALS = 'gte';
    const COMPARE_LESS_THAN_EQUALS = 'lte';
    const COMPARE_IN_ARRAY = 'in_array';
    const COMPARE_ARRAY_INTERSECT = 'array_intersect';
    const COMPARE_EQUALS = 'equals';
    const COMPARE_EQUALS_STRICT = 'equals_strict';

    public function __construct()
    {
        parent::__construct($this->getDefaults());
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            self::NAME              => '',
            self::OPERATOR          => '',
            self::IS_NOT            => false,
            self::IS_SLOT           => false,
            self::SOURCE_VALUE      => '',
            self::COMPARE_VALUE     => '',
            self::ENTITY_TYPE       => '',
            self::ENTITY_FIELD      => '',
            self::ENTITY_TYPE_FIELD => '',
        ];
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->data[self::NAME] = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->data[self::NAME];
    }

    /**
     * @param string $operator
     * @return $this
     */
    public function setOperator($operator)
    {
        $this->data[self::OPERATOR] = $operator;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->data[self::OPERATOR];
    }

    /**
     * @param bool $isNot
     * @return $this
     */
    public function setIsNot($isNot)
    {
        $this->data[self::IS_NOT] = (bool) $isNot;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsNot()
    {
        return (bool) $this->data[self::IS_NOT];
    }

    /**
     * @param bool $isSlot
     * @return $this
     */
    public function setIsSlot($isSlot)
    {
        $this->data[self::IS_SLOT] = (bool) $isSlot;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSlot()
    {
        return (bool) $this->data[self::IS_SLOT];
    }

    /**
     * @param $sourceValue
     * @return $this
     */
    public function setSourceValue($sourceValue)
    {
        $this->data[self::SOURCE_VALUE] = $sourceValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceValue()
    {
        return $this->data[self::SOURCE_VALUE];
    }

    /**
     * @param $compareValue
     * @return $this
     */
    public function setCompareValue($compareValue)
    {
        $this->data[self::COMPARE_VALUE] = $compareValue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompareValue()
    {
        return $this->data[self::COMPARE_VALUE];
    }

    /**
     * @param string $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->data[self::ENTITY_TYPE] = $entityType;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityType()
    {
        return $this->data[self::ENTITY_TYPE];
    }

    /**
     * @param string $entityField
     * @return $this
     */
    public function setEntityField($entityField)
    {
        $this->data[self::ENTITY_FIELD] = $entityField;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityField()
    {
        return $this->data[self::ENTITY_FIELD];
    }

    /**
     * @param string $entityTypeField
     * @return $this
     */
    public function setEntityTypeField($entityTypeField)
    {
        $this->data[self::ENTITY_TYPE_FIELD] = $entityTypeField;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntityTypeField()
    {
        return $this->data[self::ENTITY_TYPE_FIELD];
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isValid()
    {
        $explodedCompareValue = [];
        $explodedSourceValue = [];

        if (in_array($this->getOperator(), [self::COMPARE_IN_ARRAY, self::COMPARE_ARRAY_INTERSECT])) {
            $compareValueStr = $this->getCompareValue();
            if (is_array($compareValueStr)) {
                $explodedCompareValue = $compareValueStr;
            } else {
                $explodedCompareValue = $this->explodeCsv($compareValueStr);
            }

            if ($this->getOperator() == self::COMPARE_ARRAY_INTERSECT) {
                if (!is_array($this->getSourceValue())) {
                    $explodedSourceValue = $this->explodeCsv($this->getSourceValue());
                } else {
                    $explodedSourceValue = $this->getSourceValue();
                }
            }
        }

        switch($this->getOperator()) {
            case self::COMPARE_EQUALS:
                $result = ($this->getSourceValue() == $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::COMPARE_EQUALS_STRICT:
                $result = ($this->getSourceValue() === $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::COMPARE_GREATER_THAN:
                $result = ((float) $this->getSourceValue() > (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::COMPARE_LESS_THAN:
                $result = ((float) $this->getSourceValue() < (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::COMPARE_GREATER_THAN_EQUALS:
                $result = ((float) $this->getSourceValue() >= (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::COMPARE_LESS_THAN_EQUALS:
                $result = ((float) $this->getSourceValue() <= (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::COMPARE_IN_ARRAY:
                $result = in_array($this->getSourceValue(), $explodedCompareValue);
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::COMPARE_ARRAY_INTERSECT:
                $result = (count(array_intersect($explodedSourceValue, $explodedCompareValue)) > 0);
                return $this->getIsNot() ? !$result : $result;
                break;
            default:
                //no-op
                break;
        }

        throw new \Exception("Invalid Operator");
    }

    /**
     * Explode and trim
     *
     * @param string
     * @param bool
     * @return array
     */
    public function explodeCsv($compareValueStr, $trim = true)
    {
        $explodedCompareValue = explode(',', $compareValueStr);
        if (!count($explodedCompareValue)) {
            return [];
        }

        if ($trim) {
            foreach($explodedCompareValue as $idx => $val) {
                $explodedCompareValue[$idx] = trim($val);
            }
        }
        
        return $explodedCompareValue;
    }
}
