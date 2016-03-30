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

use MobileCart\CartComponentBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class MysqlPdoEntityService
 * @package MobileCart\CoreBundle\Service
 *
 * This service is useful for mass inserts and updates
 *  but is not very useful for individual inserts and updates
 *  because all of the item vars and options need to be loaded
 *  in order to perform an insert or update
 *
 */
class MysqlPdoEntityService
    extends DoctrineEntityService
    implements UserProviderInterface
{

    protected $conn;

    protected $varsData;

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
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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

        // return fetchAll
        return $stmt->fetch(\PDO::FETCH_ASSOC);
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

        // return fetchAll
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed|void
     */
    public function remove($entity, $objectType = '')
    {
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
     * @param $entityData
     * @param string $objectType
     * @return mixed|void
     * @throws \InvalidArgumentException
     */
    public function persist($entityData, $objectType = '')
    {
        if (!is_array($entityData)) {
            throw new \InvalidArgumentException("This method requires an associative array");
        }

        switch($objectType) {
            case EntityConstants::PRODUCT:

                if (!isset($entityData['sku'])) {
                    throw new \InvalidArgumentException("Products require a sku");
                }

                if (!isset($entityData['id'])) {
                    $id = $this->getProductIdBySku($entityData['sku']);
                    if ($id) {
                        $entityData['id'] = $id;
                    }
                }

                break;
            default:

                break;
        }

        $instance = $this->getInstance($objectType);
        $baseDataKeys = array_keys($instance->getBaseData());
        $tableName = $this->getTableName($objectType);
        $baseDataValues = [];
        //$isEav = $this->getRepository($objectType)->isEAV();

        if (isset($entityData['id']) && $entityData['id'] > 0) {

            $id = $entityData['id'];
            unset($entityData['id']);

            // build update sql

            $sql = "update {$tableName} set"; //where `id`=?

            $eavValues = [];
            foreach($entityData as $k => $v) {

                if (!in_array($k, $baseDataKeys)) {
                    $eavValues[$k] = $v;
                    continue;
                }

                $baseDataValues[$k] = $v;
                $sql .= " `{$k}`=?";
            }

            $sql .= " where `id`=?";

            $stmt = $this->conn->prepare($sql);

            // bind params

            $x = 1;
            foreach($baseDataValues as $v) {
                if (is_int($v)) {
                    $stmt->bindParam($x, $v, \PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($x, $v, \PDO::PARAM_STR);
                }
                $x++;
            }

            $stmt->bindParam($x, $id, \PDO::PARAM_INT);
            $stmt->execute();

            return [
                'op' => 'u',
                'id' => $id,
            ];

        } else {

            $sql = "insert into {$tableName} (id";
            $baseDataValues = [];
            $x = 0;
            foreach($baseDataKeys as $k) {

                if (!isset($entityData[$k])) {
                    continue;
                }

                if ($k == 'id') {
                    continue;
                }

                $sql .= ",`{$k}`";
                $baseDataValues[$k] = $entityData[$k];
                $x++;
            }

            $sql .= ') values (NULL';

            foreach($baseDataValues as $v) {
                $sql .= ", ?";
            }

            $sql .= ')';

            $stmt = $this->conn->prepare($sql);

            $x = 1;
            foreach($baseDataValues as $k => $v) {
                if (is_int($v)) {
                    $stmt->bindParam($x, $v, \PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($x, $v, \PDO::PARAM_STR);
                }

                $x++;
            }

            $stmt->execute();

            return [
                'op' => 'i',
                'id' => $this->conn->lastInsertId(),
            ];
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
        $repo = $this->getRepository($objectType);

        $id = $entity->getId();
        if (isset($formData['id'])) {
            if (!$id) {
                $id = $formData['id'];
            }
            unset($formData['id']);
        }

        switch($objectType) {
            case EntityConstants::PRODUCT:

                if (!isset($entityData['sku'])) {
                    throw new \InvalidArgumentException("Products require a sku");
                }

                if (!isset($entityData['id'])) {
                    $id = $this->getProductIdBySku($entityData['sku']);
                    if ($id) {
                        $entityData['id'] = $id;
                    }
                }

                break;
            default:

                break;
        }

        $instance = $this->getInstance($objectType);
        $baseDataKeys = array_keys($instance->getBaseData());
        $tableName = $this->getTableName($objectType);

        // build insert sql

        $sql = "insert into {$tableName} (id";
        $baseDataValues = [];

        $eavValues = [];
        foreach($formData as $k => $v) {

            if (in_array($k, $baseDataKeys)) {
                $baseDataValues[$k] = $v;
                continue;
            }

            $eavValues[$k] = $v;
            $sql .= ",{$k}";
        }

        $sql .= ') values (NULL';

        foreach($eavValues as $v) {
            $sql .= ", ?";
        }

        $sql .= ')';

        $stmt = $this->conn->prepare($sql);

        $x = 1;
        foreach($baseDataValues as $k => $v) {
            if (is_int($v)) {
                $stmt->bindParam($x, $v, \PDO::PARAM_INT);
            } else {
                $stmt->bindParam($x, $v, \PDO::PARAM_STR);
            }

            $x++;
        }

        $stmt->execute();

        // if it's EAV

        // loop on values which are not baseData

        // build insert queries, do 1 insert for each data type

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

        // get all EAV rows, by datatype
        foreach(EntityConstants::getDatatypes() as $code => $label) {

        }

        // loop on values which are not baseData

        // set aside rows which need to be inserted

        // set aside rows which need to be deleted

        // if there is not a row for the value

        return $this;
    }
}
