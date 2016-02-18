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

class RuleConditionCompare extends ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{

    const OP_AND = 'and';

    const OP_OR = 'or';

    public function __construct()
    {
        parent::__construct($this->getDefaults());
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return array(
            'operator'   => self::OP_AND,
            'conditions' => array(),
        );
    }

    /**
     * Export object as array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'operator'   => $this->getOperator(),
            'conditions' => $this->getConditionsAsArray(),
        );
    }

    /**
     * @return array
     */
    public function getValidOperators()
    {
        return array('and', 'or');
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
        $operator = isset($data['operator']) ? $data['operator'] : '';
        $conditions = isset($data['conditions']) ? $data['conditions'] : array();

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

                if (isset($data['conditions'])) {
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
            case self::OP_AND:
                if (count($this->getConditions())) {
                    foreach($this->getConditions() as $condition) {
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
            default:
                //no-op
                break;
        }
        
        throw new \Exception("Invalid Operator");
    }
}
