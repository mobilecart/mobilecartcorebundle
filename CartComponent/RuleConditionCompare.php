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
 * Class RuleConditionCompare
 * @package MobileCart\CoreBundle\CartComponent
 */
class RuleConditionCompare extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    const OPERATOR = 'operator';
    const CONDITIONS = 'conditions';

    const OP_AND = 'and';
    const OP_OR = 'or';
    const OP_HAS_PRODUCT = 'product';
    const OP_HAS_CUSTOMER = 'customer';
    const OP_HAS_SHIPMENT = 'shipment';
    const OP_CART_HAS = 'cart';

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
            self::OPERATOR   => self::OP_AND,
            self::CONDITIONS => [],
        ];
    }

    /**
     * @param string $operator
     * @return $this
     * @throws \Exception
     */
    public function setOperator($operator)
    {
        if (!in_array($operator, $this->getValidOperators())) {
            throw new \Exception("Invalid Operator Specified");
        }

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
     * @param array $conditions
     * @return $this
     */
    public function setConditions(array $conditions)
    {
        $this->data[self::CONDITIONS] = $conditions;
        return $this;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->data[self::CONDITIONS];
    }

    /**
     * Export object as array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::OPERATOR   => $this->getOperator(),
            self::CONDITIONS => $this->getConditionsAsArray(),
        ];
    }

    /**
     * @return array
     */
    public function getValidOperators()
    {
        return [
            self::OP_AND,
            self::OP_OR,
            self::OP_HAS_CUSTOMER,
            self::OP_HAS_PRODUCT,
            self::OP_HAS_SHIPMENT,
            self::OP_CART_HAS,
            Discount::TARGET_ALL_SHIPMENTS,
            Discount::TARGET_ALL_ITEMS,
        ];
    }

    /**
     * @return bool
     */
    public function hasValidOperator()
    {
        return in_array($this->getOperator(), $this->getValidOperators());
    }

    /**
     * @param $json
     * @return RuleConditionCompare
     */
    public function fromJson($json)
    {
        return $this->fromArray((array) json_decode($json));
    }

    /**
     * @param array $data
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function fromArray(array $data)
    {
        $operator = isset($data[self::OPERATOR]) ? $data[self::OPERATOR] : '';
        $conditions = isset($data[self::CONDITIONS]) ? $data[self::CONDITIONS] : [];

        /*

        [operator] => 'and'
        [conditions] => array(
            //condition
            [0] => array(
                'compare_value' => 'X',
                ...
            ),
            //condition compare
            [1] => array(
                'conditions' => array(
                    [0] => array(
                        'compare_value' => 'X',
                        ...
                    ),
                    [1] => array(
                        'compare_value' => 'X',
                        ...
                    ),
                )
            )
        )

        //*/

        $newConditions = array();
        if (is_array($conditions) && count($conditions) > 0) {
            foreach($conditions as $data) {

                if ($data instanceof \stdClass) {
                    $data = get_object_vars($data);
                }

                if (isset($data[self::CONDITIONS])) {
                    //we have RuleConditionCompare data
                    $compare = new RuleConditionCompare();
                    // "indirect" Recursion, dont want to over-write this current instance
                    $compare->fromArray($data);
                    $newConditions[] = $compare;
                } else {
                    //we have RuleCondition data
                    $condition = new RuleCondition();
                    $condition->fromArray($data);
                    $newConditions[] = $condition;
                }
            }
        }

        if (!in_array($operator, $this->getValidOperators())) {
            $operator = self::OP_AND;
        }

        $this->setOperator($operator)
             ->setConditions($newConditions);

        return $this;
    }

    /**
     * Export array of conditions as array
     *
     * @return array
     */
    public function getConditionsAsArray()
    {
        $conditions = array();
        if (count($this->getConditions()) > 0) {
            foreach($this->getConditions() as $object) {
               if ($object instanceof RuleConditionCompare) {
                    $conditions[] = $object->getConditionsAsArray();
                } else if ($object instanceof RuleCondition) {
                    $conditions[] = $object->toArray();
                }
            }
        }

        return $conditions;
    }

    /**
     * @param Item|Customer|Shipment
     * @return bool
     * @throws \Exception
     */
    public function isValid($object)
    {
        switch($this->getOperator()) {
            case self::OP_HAS_SHIPMENT:
            case self::OP_HAS_PRODUCT:
            case self::OP_HAS_CUSTOMER:
            case self::OP_AND:
                if (count($this->getConditions())) {
                    foreach($this->getConditions() as $condition) {
                        // check for compare_value
                        if (!$object->isValidCondition($condition)) {
                            return false;
                        }
                    }
                }
                return true;
                break;
            case self::OP_OR:
                if (count($this->getConditions())) {
                    foreach($this->getConditions() as $condition) {
                        if ($object->isValidCondition($condition)) {
                            return true;
                        }
                    }
                    return false;
                }
                return true;
                break;
            case self::OP_CART_HAS:
                if (count($this->getConditions())) {
                    foreach($this->getConditions() as $condition) {
                        if (!$object->isValidCondition($condition)) {
                            return false;
                        }
                    }
                }
                return true;
                break;
            default:
                //no-op
                break;
        }
        
        throw new \Exception("Invalid Operator");
    }
}
