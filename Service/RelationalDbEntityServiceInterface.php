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

/**
 * Interface RelationalDbEntityServiceInterface
 * @package MobileCart\CoreBundle\Service
 */
interface RelationalDbEntityServiceInterface
{
    /**
     * @return $this
     */
    public function beginTransaction();

    /**
     * @return $this
     */
    public function commit();

    /**
     * @return $this
     */
    public function rollBack();

    /**
     * @param $key
     * @return mixed
     */
    public function getRepository($key);

    /**
     * @param string $objectType
     * @return mixed
     */
    public function getObjectTypeItemVars($objectType);

    /**
     * @param $objectType
     * @param $dataType
     * @return mixed
     */
    public function getVarValueRepository($objectType, $dataType);

    /**
     * @param $objectType
     * @param $dataType
     * @return string
     */
    public function getVarValueTableName($objectType, $dataType);

    /**
     * @param $dataType
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getVarOptionRepository($dataType);

    /**
     * @return array
     */
    public function getVarOptionTables();

    /**
     * @return array
     */
    public function getPdoBindTypes();

    /**
     * Create an Instance
     *
     * @param $objectType
     * @return mixed
     */
    public function getInstance($objectType);

    /**
     * @param $objectType
     * @return mixed
     */
    public function getMetadata($objectType);

    /**
     * @param $objectType
     * @return string
     */
    public function getTableName($objectType);

    /**
     * @param $objectType
     * @param $dataType
     * @return string
     */
    public function getVarValueKey($objectType, $dataType);

    /**
     * @param $objectType
     * @param $dataType
     * @return mixed
     */
    public function getVarValueInstance($objectType, $dataType);

    /**
     * @param $datatype
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getVarOptionInstance($datatype);

    /**
     * @param string $objectType
     * @param $id
     * @return mixed
     */
    public function find($objectType, $id);

    /**
     * @param $objectType
     * @param array $params
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @return mixed
     */
    public function findBy($objectType, $params = [], array $orderBy = null, $limit = null, $offset = null);

    /**
     * @param $objectType
     * @param array $params
     * @param array $orderBy
     * @return mixed
     */
    public function findOneBy($objectType, array $params, array $orderBy = null);

    /**
     * @param $objectType
     * @return mixed
     */
    public function findAll($objectType);

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed
     */
    public function remove($entity, $objectType = '');

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed
     */
    public function persist($entity, $objectType = '');

    /**
     * @param string $varSetId
     * @return int
     */
    public function getVarSet($varSetId);

    /**
     * @param string $typeFilter
     * @return mixed
     */
    public function getVarSets($typeFilter = '');

    /**
     * @param $objectType
     * @param object|int $entity
     * @return array
     */
    public function getVarValues($objectType, $entity);

    /**
     * Populate var values for a list of IDs
     *  and use the minimum number of queries possible
     *
     * @param $objectType
     * @param $values
     * @return $this
     */
    public function populateVarValues($objectType, &$values);

    /**
     * Update EAV for a single entity
     *
     * @param $entity
     * @param array $data
     * @return $this
     */
    public function persistVariants($entity, array $data);

    /**
     * @param $entity
     * @param $itemVar
     * @param $value
     * @param $varValueObjectType
     * @param $varOptionObjectType
     * @return $this
     */
    public function persistSelectVariant($entity, $itemVar, $value, $varValueObjectType, $varOptionObjectType);

    /**
     * This method is for persisting values for a single entity and variant
     *
     * @param $entity
     * @param $itemVar
     * @param $values
     * @param $varValueObjectType
     * @param $varOptionObjectType
     * @return $this
     */
    public function persistMultiselectVariant($entity, $itemVar, $values, $varValueObjectType, $varOptionObjectType);
}
