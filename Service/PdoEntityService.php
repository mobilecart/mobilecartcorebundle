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

use Symfony\Component\Security\Core\User\UserProviderInterface;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class PdoEntityService
 * @package MobileCart\CoreBundle\Service
 *
 * This service is useful for mass inserts and updates
 *  but is not very useful for individual inserts and updates
 *  because all of the item vars and options need to be loaded
 *  in order to perform an insert or update
 *
 */
class PdoEntityService
    extends DoctrineEntityService
    implements UserProviderInterface
{

    protected $conn;

    protected $varsData;

    protected $foreignKeys = ['item_var_set_id', 'product_id', 'parent_id', 'category_id'];

    /**
     * @param $doctrine
     * @return $this
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
        $this->conn = $doctrine->getManager()->getConnection();
        return $this;
    }

    /**
     * @return $this
     */
    public function loadVarsAndOptions()
    {
        $itemVars = $this->findAll(EntityConstants::ITEM_VAR);
        if ($itemVars) {
            foreach($itemVars as $itemVar) {
                $this->varsData[$itemVar->getCode()] = $itemVar->getBaseData();
                $this->varsData[$itemVar->getCode()]['item_vars'] = [];
                $itemVarOptions = $itemVar->getItemVarOptions();
                if ($itemVarOptions) {
                    foreach($itemVarOptions as $itemVarOption) {
                        $this->varsData[$itemVar->getCode()]['item_vars'][$itemVarOption->getValue()] = $itemVarOption->getBaseData();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param string $objectType
     * @param $id
     * @return mixed
     */
    public function find($objectType, $id)
    {
        $tableName = $this->getTableName($objectType);
        $sql = "select * from {$tableName} where id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        // return fetchAll
        return is_array($result)
            ? new ArrayWrapper($result)
            : $result;
    }

    /**
     * @param $objectType
     * @param array $params
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @return mixed
     */
    public function findBy($objectType, $params = [], array $orderBy = null, $limit = null, $offset = null)
    {
        // build select sql
        $tableName = $this->getTableName($objectType);
        $sql = "select * from {$tableName} where";
        if ($params) {
            $x = 0;
            foreach($params as $k => $v) {
                if ($x > 0) {
                    $sql .= ' AND';
                }

                if (is_array($v)) {
                    $sql .= " in (" . implode(',', $v) . ")";
                } else {
                    $sql .= " {$k} = ?";
                }

                $x++;
            }
        } else {
            $sql .= ' 1=1';
        }

        if ($orderBy) {
            $sql .= " order by " . $orderBy[0] . " " . $orderBy[1];
        }

        // offset, limit
        if (is_int($limit)) {
            if (is_int($offset)) {
                $sql .= " limit {$offset}, {$limit}";
            } else {
                $sql .= " limit {$limit}";
            }
        }

        $stmt = $this->conn->prepare($sql);

        // bind params
        if ($params) {
            $x = 1;
            foreach($params as $v) {
                if (is_int($v)) {
                    $stmt->bindParam($x, $v, \PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($x, $v, \PDO::PARAM_STR);
                }
                $x++;
            }
        }

        // execute
        $stmt->execute();

        // return fetchAll
        //return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows) {
            foreach($rows as $k => $v) {
                $rows[$k] = new ArrayWrapper($v);
            }
        }
        return $rows;
    }

    /**
     * @param $objectType
     * @param array $params
     * @param array $orderBy
     * @return mixed
     */
    public function findOneBy($objectType, array $params, array $orderBy = null)
    {
        // build select sql
        $tableName = $this->getTableName($objectType);
        $sql = "select * from {$tableName} where";
        if ($params) {
            $x = 0;
            foreach($params as $k => $v) {
                if ($x > 0) {
                    $sql .= ' AND';
                }

                if (is_array($v)) {
                    $sql .= " in (" . implode(',', $v) . ")";
                } else {
                    $sql .= " {$k} = ?";
                }

                $x++;
            }
        } else {
            $sql .= ' 1=1';
        }

        if ($orderBy) {
            $sql .= " order by " . $orderBy[0] . " " . $orderBy[1];
        }

        $sql .= " limit 1";

        $stmt = $this->conn->prepare($sql);

        // bind params
        if ($params) {
            $x = 1;
            foreach($params as $v) {
                if (is_int($v)) {
                    $stmt->bindParam($x, $v, \PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($x, $v, \PDO::PARAM_STR);
                }
                $x++;
            }
        }

        // execute
        $stmt->execute();

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        // return fetchAll
        return is_array($result)
            ? new ArrayWrapper($result)
            : $result;
    }

    /**
     * @param $objectType
     * @return array
     */
    public function findAll($objectType)
    {
        // retrieve all rows from a table
        // build select sql
        $tableName = $this->getTableName($objectType);
        $sql = "select * from {$tableName} where 1=1";

        // build select sql
        $stmt = $this->conn->prepare($sql);

        // execute
        $stmt->execute();

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($rows) {
            foreach($rows as $k => $v) {
                $rows[$k] = new ArrayWrapper($v);
            }
        }
        return $rows;
    }

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed|void
     */
    public function remove($entity, $objectType = '')
    {
        if (is_array($entity)) {
            $entity = new ArrayWrapper($entity);
        }

        // build delete query
        $id = $entity->getId();

        $tableName = $this->getTableName($objectType);
        $sql = "delete from {$tableName} where id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $id, \PDO::PARAM_INT);
        $stmt->execute();

        // determine if it's EAV, the delete should cascade
        //  might need to delete those rows manually

        // return true or false
    }

    /**
     * @param $sku
     * @return int
     */
    public function getProductIdBySku($sku)
    {
        $table = $this->getTableName(EntityConstants::PRODUCT);
        $sql = "select id from {$table} where sku = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(1, $sku, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return isset($row['id'])
            ? $row['id']
            : 0;
    }

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed|void
     * @throws \InvalidArgumentException
     */
    public function persist($entity, $objectType = '')
    {
        if (is_array($entity)) {
            $entity = new ArrayWrapper($entity);
        }

        if (!$objectType) {
            if (!$entity->getObjectType()) {
                throw new \InvalidArgumentException("Invalid ObjectType");
            }
            $objectType = $entity->getObjectType();
        }

        $entityData = ($entity instanceof ArrayWrapper)
            ? $entity->getData()
            : $entity->getBaseData();

        // todo : remove key object_type for correct entities

        // todo : pull this out
        switch($objectType) {
            case EntityConstants::PRODUCT:

                if (!$entity->getSku()) {
                    throw new \InvalidArgumentException("Products require a sku");
                }

                if (!isset($entityData['id'])) {
                    $id = $this->getProductIdBySku($entity->getSku());
                    if ($id) {
                        $entityData['id'] = $id;
                    }
                }

                break;
            default:

                break;
        }

        $repo = $this->getRepository($objectType);
        $instance = $this->getInstance($objectType);
        $baseDataKeys = $repo->isEAV()
            ? array_keys($instance->getBaseData())
            : [];

        $tableName = $this->getTableName($objectType);

        if (isset($entityData['id']) && $entityData['id'] > 0) {

            $id = $entityData['id'];

            $sql = "update {$tableName} set";

            //$eavValues = [];
            $parts = [];
            foreach($entityData as $k => $v) {

                if (
                    $repo->isEAV()
                    && !in_array($k, $baseDataKeys)
                    && !in_array($k, $this->foreignKeys)
                ) {
                    unset($entityData[$k]);
                    continue;
                }

                $parts[] = " `{$k}`=:{$k}";;
            }

            $sql .= implode(',', $parts);
            $sql .= " where `id`=:id";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute($entityData);

            return $id;
        } else {

            $sql = "insert into {$tableName} (id";
            foreach($entityData as $k => $v) {

                // todo : ensure this doesnt happen
                if ($k == 'id') {
                    unset($entityData[$k]);
                    continue;
                }

                if (
                    $repo->isEAV()
                    && !in_array($k, $baseDataKeys)
                    && !in_array($k, $this->foreignKeys)
                ) {
                    unset($entityData[$k]);
                    continue;
                }

                if (is_array($entityData[$k])) {
                    unset($entityData[$k]);
                    continue;
                }

                $sql .= ",`{$k}`";
            }

            $sql .= ') values (NULL';

            foreach($entityData as $k => $v) {
                $sql .= ", :{$k}";
            }

            $sql .= ')';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($entityData);

            return $this->conn->lastInsertId();
        }
    }

    public function handleVarValueSave($objectType, $entity, array $data)
    {
        if (is_array($entity)) {
            $entity = new ArrayWrapper($entity);
        }

        // separate base data from variant data
        $instance = $this->getInstance($objectType);
        $baseDataKeys = array_keys($instance->getBaseData());
        $entityId = $entity->getId();
        if (!$entityId && isset($data['id'])) {
            $entityId = $data['id'];
            unset($data['id']);
        }

        // loop on variant data
        foreach($data as $k => $v) {

            if (in_array($k, $baseDataKeys)) {
                unset($data[$k]);
                continue;
            }

            // load variant and determine input type
            $itemVar = $this->findOneBy(EntityConstants::ITEM_VAR, [
                'code' => $k,
            ]);

            if (!$itemVar) {
                continue;
            }

            $varValueObjectType = $this->getVarValueKey($objectType, $itemVar['datatype']);

            switch($itemVar['input_type']) {
                case EntityConstants::INPUT_SELECT:

                    $varOptionObjectType = '';

                    switch($itemVar['datatype']) {
                        case EntityConstants::DATETIME:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_DATETIME;
                            break;
                        case EntityConstants::DECIMAL:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_DECIMAL;
                            break;
                        case EntityConstants::INT:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_INT;
                            break;
                        case EntityConstants::VARCHAR:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_VARCHAR;
                            break;
                        case EntityConstants::TEXT:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_TEXT;
                            break;
                        default:
                            continue;
                            break;
                    }

                    $varOption = $this->findOneBy($varOptionObjectType, [
                        'item_var_id' => $itemVar['id'],
                        'value' => $v
                    ]);

                    $varOptionId = $varOption
                        ? $varOption['id']
                        : 0;

                    if (!$varOption) {
                        // save new option

                        $varOptionId = $this->persist([
                            'item_var_id' => $itemVar['id'],
                            'value' => $v,
                            'url_value' => $this->slugify($v),
                        ], $varOptionObjectType);

                        // insert new row in x_var_value_y
                        $this->persist([
                            'item_var_id' => $itemVar['id'],
                            'item_var_option_id' => $varOptionId,
                            'parent_id' => $entityId,
                            'value' => $v,
                        ], $varValueObjectType);

                    } else {

                        $varValueData = [
                            'item_var_id' => $itemVar['id'],
                            'item_var_option_id' => $varOptionId,
                            'parent_id' => $entityId,
                            'value' => $v,
                        ];

                        // look for for row
                        $varValue = $this->findOneBy($varValueObjectType, [
                            'parent_id' => $entityId,
                            'item_var_id' => $itemVar['id'],
                            'item_var_option_id' => $varOptionId,
                        ]);

                        if ($varValue) {
                            $varValueData['id'] = $varValue['id'];
                        }

                        $this->persist($varValueData, $varValueObjectType);
                    }

                    break;
                case EntityConstants::INPUT_MULTISELECT:

                    $varOptionObjectType = '';

                    switch($itemVar['datatype']) {
                        case EntityConstants::DATETIME:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_DATETIME;
                            break;
                        case EntityConstants::DECIMAL:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_DECIMAL;
                            break;
                        case EntityConstants::INT:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_INT;
                            break;
                        case EntityConstants::VARCHAR:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_VARCHAR;
                            break;
                        case EntityConstants::TEXT:
                            $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_TEXT;
                            break;
                        default:
                            continue;
                            break;
                    }

                    $varValues = $this->findBy($varOptionObjectType, [
                        'parent_id' => $entityId,
                        'item_var_id' => $itemVar['id'],
                    ]);

                    if (!is_array($v)) {
                        $v = [$v];
                    }

                    foreach($v as $varValue) {

                        $varOption = $this->findOneBy($varOptionObjectType, [
                            'item_var_id' => $itemVar['id'],
                            'value' => $varValue
                        ]);

                        $varOptionId = $varOption
                            ? $varOption['id']
                            : 0;

                        if (!$varOption) {
                            // save new option

                            $varOptionId = $this->persist([
                                'item_var_id' => $itemVar['id'],
                                'value' => $v,
                                'url_value' => $this->slugify($v),
                            ], $varOptionObjectType);

                            // insert new row in x_var_value_y
                            $this->persist([
                                'item_var_id' => $itemVar['id'],
                                'item_var_option_id' => $varOptionId,
                                'parent_id' => $entityId,
                                'value' => $v,
                            ], $varValueObjectType);

                        } else {

                            // figure out if we already have this value, avoid a duplicate being saved
                            $exists = false;
                            foreach($varValues as $k => $aVarValue) {
                                if ($aVarValue['value'] == $varValue) {
                                    unset($varValues[$k]);
                                    $exists = true;
                                    break;
                                }
                            }

                            if ($exists) {
                                continue;
                            }

                            $varValueData = [
                                'item_var_id' => $itemVar['id'],
                                'item_var_option_id' => $varOptionId,
                                'parent_id' => $entityId,
                                'value' => $varValue,
                            ];

                            // look for for row
                            $this->findOneBy($varValueObjectType, [
                                'parent_id' => $entityId,
                                'item_var_id' => $itemVar['id'],
                                'item_var_option_id' => $varOptionId,
                            ]);

                            $this->persist($varValueData, $varValueObjectType);
                        }

                        if ($varValues) {
                            foreach($varValues as $aVarValue) {
                                $this->remove(new ArrayWrapper($aVarValue), $varValueObjectType);
                            }
                        }
                    }

                    break;
                default:

                    $varValueData = [
                        'item_var_id' => $itemVar['id'],
                        'parent_id' => $entityId,
                        'value' => $v,
                    ];

                    // look for for row
                    $varValue = $this->findOneBy($varValueObjectType, [
                        'parent_id' => $entityId,
                        'item_var_id' => $itemVar['id'],
                    ]);

                    if ($varValue) {
                        $varValueData['id'] = $varValue['id'];
                    }

                    $this->persist($varValueData, $varValueObjectType);

                    break;
            }
        }
    }

    /**
     * Create EAV Values for a newly created Entity
     *  only creates rows for submitted vars
     *
     * @param $objectType
     * @param $entity
     * @param array $formData
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function handleVarValueCreate($objectType, $entity, array $formData)
    {
        $this->handleVarValueSave($objectType, $entity, $formData);
        return $this;
    }

    /**
     * Update EAV Values for an existing Entity
     *  look for existing rows and remove if necessary
     *
     * @param $objectType
     * @param $entity
     * @param array $formData
     * @return $this
     * @throws \Exception
     */
    public function handleVarValueUpdate($objectType, $entity, array $formData)
    {
        $this->handleVarValueSave($objectType, $entity, $formData);
        return $this;
    }
}
