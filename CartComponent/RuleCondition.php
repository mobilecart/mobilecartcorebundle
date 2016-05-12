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

class RuleCondition extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    // array key values
    static $compareGreaterThan = 'gt';
    static $compareLessThan = 'lt';
    static $compareGreaterThanEquals = 'gte';
    static $compareLessThanEquals = 'lte';
    static $compareInArray = 'in_array';
    static $compareArrayIntersect = 'array_intersect';
    static $compareEquals = 'equals';
    static $compareEqualsStrict = 'equals_strict';

    public function __construct()
    {
        parent::__construct($this->getDefaults());
    }

    public function getDefaults()
    {
        return array(
            'name'              => '',
            'operator'          => '',
            'is_not'            => false,
            'is_slot'           => false,
            'source_value'      => '',
            'compare_value'     => '',
            'entity_type'       => '',
            'entity_field'      => '',
            'entity_field_type' => '',
        );
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isValid()
    {
        $explodedCompareValue = array();
        $explodedSourceValue = array();

        if (in_array($this->getOperator(), array(self::$compareInArray, self::$compareArrayIntersect))) {
            $compareValueStr = $this->getCompareValue();
            if (is_array($compareValueStr)) {
                $explodedCompareValue = $compareValueStr;
            } else {
                $explodedCompareValue = $this->explodeCsv($compareValueStr);
            }

            if ($this->getOperator() == self::$compareArrayIntersect) {
                if (!is_array($this->getSourceValue())) {
                    $explodedSourceValue = $this->explodeCsv($this->getSourceValue());
                } else {
                    $explodedSourceValue = $this->getSourceValue();
                }
            }
        }

        switch($this->getOperator()) {
            case self::$compareEquals:
                $result = ($this->getSourceValue() == $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareEqualsStrict:
                $result = ($this->getSourceValue() === $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareGreaterThan:
                $result = ((float) $this->getSourceValue() > (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareLessThan:
                $result = ((float) $this->getSourceValue() < (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareGreaterThanEquals:
                $result = ((float) $this->getSourceValue() >= (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareLessThanEquals:
                $result = ((float) $this->getSourceValue() <= (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareInArray:
                $result = in_array($this->getSourceValue(), $explodedCompareValue);
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareArrayIntersect:
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
            return array();
        }

        if ($trim) {
            foreach($explodedCompareValue as $idx => $val) {
                $explodedCompareValue[$idx] = trim($val);
            }
        }
        
        return $explodedCompareValue;
    }
}
