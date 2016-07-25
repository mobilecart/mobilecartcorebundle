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

class DoctrineEntityService
    extends AbstractEntityService
    implements UserProviderInterface
{
    /**
     * @var EntityManager
     */
    protected $doctrine;

    /**
     * @param $doctrine
     * @return $this
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRepository($key)
    {
        return $this->getDoctrine()->getManager()
            ->getRepository($this->getObjectRepository($key));
    }

    /**
     * @param $objectType
     * @return array|void
     */
    public function getObjectTypeItemVars($objectType)
    {
        $itemVarRepo = $this->getObjectRepository(EntityConstants::ITEM_VAR);
        $itemVarSetVarRepo = $this->getObjectRepository(EntityConstants::ITEM_VAR_SET_VAR);
        $itemVarSetRepo = $this->getObjectRepository(EntityConstants::ITEM_VAR_SET);

        $dql = "select distinct iv from {$itemVarRepo} iv".
            " join {$itemVarSetVarRepo} ivsv with iv.id=ivsv.item_var".
            " join {$itemVarSetRepo} ivs with ivsv.item_var_set=ivs.id".
            " where".
            " ivs.object_type=:objectType";

        $query = $this->getDoctrine()
            ->getManager()
            ->createQuery($dql);

        $query->setParameter('objectType', $objectType);

        return $query->getResult();
    }

    /**
     * @param $objectType
     * @param $dataType
     * @return mixed
     */
    public function getVarValueRepository($objectType, $dataType)
    {
        return $this->getRepository($this->getVarValueKey($objectType, $dataType));
    }

    /**
     * @param $objectType
     * @param $dataType
     * @return mixed
     */
    public function getVarValueTableName($objectType, $dataType)
    {
        return $this->getTableName($this->getVarValueKey($objectType, $dataType));
    }

    /**
     * @param $dataType
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getVarOptionRepository($dataType)
    {
        switch($dataType) {
            case EntityConstants::DATETIME:
                return $this->getRepository(EntityConstants::ITEM_VAR_OPTION_DATETIME);
                break;
            case EntityConstants::DECIMAL:
                return $this->getRepository(EntityConstants::ITEM_VAR_OPTION_DECIMAL);
                break;
            case EntityConstants::INT:
                return $this->getRepository(EntityConstants::ITEM_VAR_OPTION_INT);
                break;
            case EntityConstants::TEXT:
                return $this->getRepository(EntityConstants::ITEM_VAR_OPTION_TEXT);
                break;
            case EntityConstants::VARCHAR:
                return $this->getRepository(EntityConstants::ITEM_VAR_OPTION_VARCHAR);
                break;
            default:

                break;
        }

        throw new \InvalidArgumentException("Invalid Data Type specified");
    }

    /**
     * @return array
     */
    public function getVarOptionTables()
    {
        return [
            EntityConstants::DATETIME => $this->getTableName(EntityConstants::ITEM_VAR_OPTION_DATETIME),
            EntityConstants::DECIMAL => $this->getTableName(EntityConstants::ITEM_VAR_OPTION_DECIMAL),
            EntityConstants::INT => $this->getTableName(EntityConstants::ITEM_VAR_OPTION_INT),
            EntityConstants::TEXT => $this->getTableName(EntityConstants::ITEM_VAR_OPTION_TEXT),
            EntityConstants::VARCHAR => $this->getTableName(EntityConstants::ITEM_VAR_OPTION_VARCHAR),
        ];
    }

    /**
     * @return array
     */
    public function getPdoBindTypes()
    {
        return [
            EntityConstants::DATETIME => \PDO::PARAM_STR,
            EntityConstants::DECIMAL => \PDO::PARAM_STR,
            EntityConstants::INT => \PDO::PARAM_INT,
            EntityConstants::TEXT => \PDO::PARAM_STR,
            EntityConstants::VARCHAR => \PDO::PARAM_STR,
            EntityConstants::BOOLEAN => \PDO::PARAM_INT,
        ];
    }

    /**
     * Create an Instance
     *
     * @param $objectType
     * @return mixed
     */
    public function getInstance($objectType)
    {
        $className = $this->getRepository($objectType)
            ->getClassName();

        return new $className;
    }

    /**
     * @param $objectType
     * @return mixed
     */
    public function getMetadata($objectType)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getClassMetadata($this->repos[$objectType]);
    }

    /**
     * @param $objectType
     * @return mixed
     */
    public function getTableName($objectType)
    {
        return $this->getMetadata($objectType)
            ->getTableName();
    }

    /**
     * @param $objectType
     * @param $dataType
     * @return string
     */
    public function getVarValueKey($objectType, $dataType)
    {
        return implode('_', [$objectType, 'var', 'value', strtolower($dataType)]);
    }

    /**
     * @param $objectType
     * @param $dataType
     * @return mixed
     */
    public function getVarValueInstance($objectType, $dataType)
    {
        return $this->getInstance($this->getVarValueKey($objectType, $dataType));
    }

    /**
     * @param $datatype
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getVarOptionInstance($datatype)
    {
        switch($datatype) {
            case EntityConstants::DATETIME:
                return $this->getInstance(EntityConstants::ITEM_VAR_OPTION_DATETIME);
                break;
            case EntityConstants::DECIMAL:
                return $this->getInstance(EntityConstants::ITEM_VAR_OPTION_DECIMAL);
                break;
            case EntityConstants::INT:
                return $this->getInstance(EntityConstants::ITEM_VAR_OPTION_INT);
                break;
            case EntityConstants::VARCHAR:
                return $this->getInstance(EntityConstants::ITEM_VAR_OPTION_VARCHAR);
                break;
            case EntityConstants::TEXT:
                return $this->getInstance(EntityConstants::ITEM_VAR_OPTION_TEXT);
                break;
            default:
                // no-op
                break;
        }

        throw new \InvalidArgumentException("Invalid datatype specified");
    }

    /**
     * @param string $objectType
     * @param $id
     * @return mixed
     */
    public function find($objectType, $id)
    {
        return $this->getRepository($objectType)->find($id);
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
        return $this->getRepository($objectType)->findBy($params, $orderBy, $limit, $offset);
    }

    /**
     * @param $objectType
     * @param array $params
     * @param array $orderBy
     * @return mixed
     */
    public function findOneBy($objectType, array $params, array $orderBy = null)
    {
        return $this->getRepository($objectType)->findOneBy($params, $orderBy);
    }

    /**
     * @param $objectType
     * @return array
     */
    public function findAll($objectType)
    {
        return $this->getRepository($objectType)->findAll();
    }

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed|void
     */
    public function remove($entity, $objectType = '')
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @param $entity
     * @param string $objectType
     * @return mixed|void
     */
    public function persist($entity, $objectType = '')
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * @param string $varSetId
     * @return int
     */
    public function getVarSet($varSetId)
    {
        return $this->getRepository(EntityConstants::ITEM_VAR_SET)->find($varSetId);
    }

    /**
     * @param string $typeFilter
     * @return mixed
     */
    public function getVarSets($typeFilter = '')
    {
        return $this->getRepository(EntityConstants::ITEM_VAR_SET)->findBy([
            'object_type' => $typeFilter,
        ]);
    }

    /**
     * @param $objectType
     * @param object|int $entity
     * @return array
     */
    public function getVarValues($objectType, $entity)
    {
        if (is_int($entity)) {
            $entity = $this->find($objectType, $entity);
        }

        $values = $entity->getBaseData();
        $em = $this->getDoctrine()->getManager();
        $iv = EntityConstants::ITEM_VAR;

        if ($entityId = $entity->getId()) {
            foreach(EntityConstants::getVarDatatypes() as $suffix) {

                // todo : use $this->getObjectRepository()
                $key = implode('_', [$objectType, $suffix]);
                //$objectRepo = $this->getObjectRepository($objectType);

                $sql = "select *" .
                    " from {$key} vo" .
                    " inner join {$iv} iv on vo.item_var_id=iv.id" .
                    " where vo.parent_id=:entityId";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindParam(':entityId', $entityId, \PDO::PARAM_INT);
                $stmt->execute();
                $dataValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if ($dataValues) {
                    foreach($dataValues as $dataValue) {
                        $dataValue = new ArrayWrapper($dataValue);
                        $code = $dataValue->getCode();
                        $value = $dataValue->getValue();
                        $formInput = $dataValue->getFormInput();
                        if ($formInput == 'multiselect') {
                            if (!isset($values[$code])) {
                                $values[$code] = [];
                            }
                            $values[$code][] = $value;
                        } else {
                            $values[$code] = $value;
                        }
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Populate var values for a list of IDs
     *  and use the minimum number of queries possible
     *
     * @param $objectType
     * @param $values
     * @return $this
     */
    public function populateVarValues($objectType, &$values)
    {
        $eavTypes = EntityConstants::getEavObjects(); // todo: move this
        if (!$values || !isset($eavTypes[$objectType])) {
            return $this;
        }

        $entityIds = [];
        foreach($values as $data) {
            if (!isset($data['id'])) {
                continue;
            }
            $entityIds[] = $data['id'];
        }

        if (!$entityIds) {
            return $this;
        }

        $em = $this->getDoctrine()->getManager();
        $map = array_flip($entityIds);
        $iv = EntityConstants::ITEM_VAR;

        if ($entityIds) {
            foreach(EntityConstants::getVarDatatypes() as $suffix) {

                $key = implode('_', [$objectType, $suffix]);

                $sql = "select *" .
                    " from {$key} vo" .
                    " inner join {$iv} iv on vo.item_var_id=iv.id" .
                    " where vo.parent_id in (" . implode(',', $entityIds) . ")";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->execute();
                $dataValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                if ($dataValues) {
                    foreach($dataValues as $dataValue) {

                        $entityId = $dataValue['parent_id'];
                        $idx = $map[$entityId];
                        $code = $dataValue['code'];
                        $value = $dataValue['value'];
                        $formInput = $dataValue['form_input'];

                        if ($formInput == 'multiselect') {
                            if (!isset($values[$idx][$code])) {
                                $values[$idx][$code] = [];
                            }
                            $values[$idx][$code][] = $value;
                        } else {
                            $values[$idx][$code] = $value;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param $objectType
     * @param $values
     * @return $this
     */
    public function populateImages($objectType, &$values)
    {
        $entityIds = [];
        foreach($values as $data) {
            if (!isset($data['id'])) {
                continue;
            }
            $entityIds[] = $data['id'];
        }

        if (!$entityIds) {
            return $this;
        }

        $imageObjectType = $objectType . '_image';
        $table = $this->getTableName($imageObjectType);
        $em = $this->getDoctrine()->getManager();
        $map = array_flip($entityIds);
        $code = 'images';

        if ($entityIds) {
            $entityIdsStr = implode(',', $entityIds);
            $sql = "select * from {$table} where parent_id in ({$entityIdsStr})";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $dataValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($dataValues) {
                foreach($dataValues as $dataValue) {

                    $entityId = $dataValue['parent_id'];
                    $idx = $map[$entityId];

                    if (!isset($values[$idx][$code])) {
                        $values[$idx][$code] = [];
                    }
                    $values[$idx][$code][] = $dataValue;
                }
            }
        }
    }

    /**
     * @param $objectType
     * @param $values
     * @param $newIdx
     * @param string $column
     * @return $this
     */
    public function populateChildData($objectType, &$values, $newIdx = '', $column = 'parent_id')
    {
        $entityIds = [];
        foreach($values as $data) {
            if (!isset($data['id'])) {
                continue;
            }
            $entityIds[] = $data['id'];
        }

        if (!$entityIds) {
            return $this;
        }

        if (!$newIdx) {
            $newIdx = $objectType . 's';
        }

        $table = $this->getTableName($objectType);
        $em = $this->getDoctrine()->getManager();
        $map = array_flip($entityIds);

        if ($entityIds) {
            $entityIdsStr = implode(',', $entityIds);
            $sql = "select * from {$table} where {$column} in ({$entityIdsStr})";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();
            $dataValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if ($dataValues) {
                foreach($dataValues as $dataValue) {

                    $entityId = $dataValue[$column];
                    $idx = $map[$entityId];

                    if (!isset($values[$idx][$newIdx])) {
                        $values[$idx][$newIdx] = [];
                    }
                    $values[$idx][$newIdx][] = $dataValue;
                }
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
     */
    public function handleVarValueCreate($objectType, $entity, array $formData)
    {
        if (!$objectType) {
            $objectType = $entity->getObjectType();
        }

        if ($formData) {

            $baseData = $entity->getBaseData();

            foreach($formData as $key => $value) {

                /**
                 * Logic overview:
                 *
                 *  Load Variant
                 *  Handle input type: multiselect, select, text
                 *  Save value(s) and possible associations according to what is expected
                 *
                 */

                if (isset($baseData[$key])) {
                    // skip un-necessary lookups
                    continue;
                }

                $pVar = false;
                if (substr($key, 0, strlen('var_')) == 'var_') {
                    $info = explode('_', $key);
                    $varId = isset($info[1]) ? $info[1] : 0;
                    if (!$varId) {
                        continue;
                    }

                    /** @var ItemVar $pVar */
                    $pVar = $this->getRepository(EntityConstants::ITEM_VAR)->find($varId);

                } else {

                    /** @var ItemVar $pVar */
                    $pVar = $this->getRepository(EntityConstants::ITEM_VAR)->findOneBy([
                        'code' => $key,
                    ]);
                }

                if (!$pVar) {
                    continue;
                }

                switch($pVar->getDatatype()) {
                    case EntityConstants::DATETIME:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::DATETIME);

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                foreach($value as $aVal) {

                                    $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DATETIME, [
                                        'value' => $aVal,
                                    ]);

                                    if ($varOption) {
                                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::DATETIME);
                                        $pVarValue->setItemVarOption($varOption);
                                        $pVarValue->setParent($entity);
                                        $pVarValue->setItemVar($pVar);
                                        $pVarValue->setValue($aVal);
                                        $this->persist($pVarValue);
                                    }
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DATETIME, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                //$value = gmdate('Y-m-d H:i:s', strtotime($value));

                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::DECIMAL:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::DECIMAL);

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                foreach($value as $aVal) {

                                    $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DECIMAL, [
                                        'value' => $aVal,
                                    ]);

                                    if ($varOption) {
                                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::DECIMAL);
                                        $pVarValue->setItemVarOption($varOption);
                                        $pVarValue->setParent($entity);
                                        $pVarValue->setItemVar($pVar);
                                        $pVarValue->setValue($aVal);
                                        $this->persist($pVarValue);
                                    }
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DECIMAL, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                // $value = (float) $value;

                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::INT:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::INT);

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                foreach($value as $aVal) {

                                    $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_INT, [
                                        'value' => $aVal,
                                    ]);

                                    if ($varOption) {
                                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::INT);
                                        $pVarValue->setItemVarOption($varOption);
                                        $pVarValue->setParent($entity);
                                        $pVarValue->setItemVar($pVar);
                                        $pVarValue->setValue($aVal);
                                        $this->persist($pVarValue);
                                    }
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_INT, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::VARCHAR:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::VARCHAR);

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                foreach($value as $aVal) {

                                    $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_VARCHAR, [
                                        'value' => $aVal,
                                    ]);

                                    if ($varOption) {
                                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::VARCHAR);
                                        $pVarValue->setItemVarOption($varOption);
                                        $pVarValue->setParent($entity);
                                        $pVarValue->setItemVar($pVar);
                                        $pVarValue->setValue($aVal);
                                        $this->persist($pVarValue);
                                    }
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_VARCHAR, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::TEXT:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::TEXT);

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                foreach($value as $aVal) {

                                    $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_TEXT, [
                                        'value' => $aVal,
                                    ]);

                                    if ($varOption) {
                                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::TEXT);
                                        $pVarValue->setItemVarOption($varOption);
                                        $pVarValue->setParent($entity);
                                        $pVarValue->setItemVar($pVar);
                                        $pVarValue->setValue($aVal);
                                        $this->persist($pVarValue);
                                    }
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_TEXT, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    default:
                        // don't save anything
                        break;
                }
            }
        }

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
        if ($formData) {

            $baseData = $entity->getBaseData();
            $currentData = $entity->getData();

            foreach($formData as $key => $value) {

                /**
                 * Logic overview:
                 *
                 *  Load Variant
                 *  Handle input type: multiselect, select, text
                 *  Save value(s) and possible associations according to what is expected
                 *
                 */

                if (isset($baseData[$key])) {
                    // skip un-necessary lookups
                    continue;
                }

                $pVar = false;
                if (substr($key, 0, strlen('var_')) == 'var_') {
                    $info = explode('_', $key);
                    $varId = isset($info[1]) ? $info[1] : 0;
                    if (!$varId) {
                        continue;
                    }

                    /** @var ItemVar $pVar */
                    $pVar = $this->getRepository(EntityConstants::ITEM_VAR)->find($varId);

                } else {

                    /** @var ItemVar $pVar */
                    $pVar = $this->getRepository(EntityConstants::ITEM_VAR)->findOneBy([
                        'code' => $key,
                    ]);
                }

                if (!$pVar) {
                    continue;
                }

                switch($pVar->getDatatype()) {
                    case EntityConstants::DATETIME:

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                $currentValues = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : [];

                                $added = array_diff($currentValues, $value);
                                $removed = array_diff($value, $currentValues);

                                foreach($value as $aVal) {

                                    if (in_array($aVal, $added)) {

                                        $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DATETIME, [
                                            'value' => $aVal,
                                        ]);

                                        if ($varOption) {
                                            $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                            $pVarValue->setItemVarOption($varOption);
                                            $pVarValue->setParent($entity);
                                            $pVarValue->setItemVar($pVar);
                                            $pVarValue->setValue($aVal);
                                            $this->persist($pVarValue);
                                        }
                                    } elseif (in_array($aVal, $removed)) {

                                        $children = $entity->getVarValuesDatetime();
                                        if ($children->count()) {
                                            foreach($children as $child) {
                                                if ($child->getItemVar()->getId() != $pVar->getId()) {
                                                    continue;
                                                }

                                                if ($child->getValue() == $aVal) {
                                                    $this->remove($child);
                                                    break;
                                                }
                                            }
                                        }
                                    } // else, nothing changed
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesDatetime();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DATETIME, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesDatetime();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::DECIMAL:

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                $currentValues = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : [];

                                $added = array_diff($currentValues, $value);
                                $removed = array_diff($value, $currentValues);

                                foreach($value as $aVal) {

                                    if (in_array($aVal, $added)) {

                                        $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DECIMAL, [
                                            'value' => $aVal,
                                        ]);

                                        if ($varOption) {
                                            $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                            $pVarValue->setItemVarOption($varOption);
                                            $pVarValue->setParent($entity);
                                            $pVarValue->setItemVar($pVar);
                                            $pVarValue->setValue($aVal);
                                            $this->persist($pVarValue);
                                        }
                                    } elseif (in_array($aVal, $removed)) {

                                        $children = $entity->getVarValuesDecimal();
                                        if ($children->count()) {
                                            foreach($children as $child) {
                                                if ($child->getItemVar()->getId() != $pVar->getId()) {
                                                    continue;
                                                }

                                                if ($child->getValue() == $aVal) {
                                                    $this->remove($child);
                                                    break;
                                                }
                                            }
                                        }
                                    } // else, nothing changed
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesDecimal();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_DECIMAL, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesDecimal();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::INT:

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                $currentValues = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : [];

                                $added = array_diff($currentValues, $value);
                                $removed = array_diff($value, $currentValues);

                                foreach($value as $aVal) {

                                    if (in_array($aVal, $added)) {

                                        $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_INT, [
                                            'value' => $aVal,
                                        ]);

                                        if ($varOption) {
                                            $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                            $pVarValue->setItemVarOption($varOption);
                                            $pVarValue->setParent($entity);
                                            $pVarValue->setItemVar($pVar);
                                            $pVarValue->setValue($aVal);
                                            $this->persist($pVarValue);
                                        }
                                    } elseif (in_array($aVal, $removed)) {

                                        $children = $entity->getVarValuesInt();
                                        if ($children->count()) {
                                            foreach($children as $child) {
                                                if ($child->getItemVar()->getId() != $pVar->getId()) {
                                                    continue;
                                                }

                                                if ($child->getValue() == $aVal) {
                                                    $this->remove($child);
                                                    break;
                                                }
                                            }
                                        }
                                    } // else, nothing changed
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesInt();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_INT, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesInt();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::VARCHAR:

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                $currentValues = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : [];

                                $added = array_diff($currentValues, $value);
                                $removed = array_diff($value, $currentValues);

                                foreach($value as $aVal) {

                                    if (in_array($aVal, $added)) {

                                        $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_VARCHAR, [
                                            'value' => $aVal,
                                        ]);

                                        if ($varOption) {
                                            $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                            $pVarValue->setItemVarOption($varOption);
                                            $pVarValue->setParent($entity);
                                            $pVarValue->setItemVar($pVar);
                                            $pVarValue->setValue($aVal);
                                            $this->persist($pVarValue);
                                        }
                                    } elseif (in_array($aVal, $removed)) {

                                        $children = $entity->getVarValuesVarchar();
                                        if ($children->count()) {
                                            foreach($children as $child) {
                                                if ($child->getItemVar()->getId() != $pVar->getId()) {
                                                    continue;
                                                }

                                                if ($child->getValue() == $aVal) {
                                                    $this->remove($child);
                                                    break;
                                                }
                                            }
                                        }
                                    } // else, nothing changed
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesVarchar();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_VARCHAR, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesVarchar();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    case EntityConstants::TEXT:

                        switch($pVar->getFormInput()) {
                            case EntityConstants::INPUT_MULTISELECT:
                                // assuming: multiple values, all associated to options

                                if (!is_array($value)) {
                                    $value = [$value];
                                }

                                $currentValues = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : [];

                                $added = array_diff($currentValues, $value);
                                $removed = array_diff($value, $currentValues);

                                foreach($value as $aVal) {

                                    if (in_array($aVal, $added)) {

                                        $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_TEXT, [
                                            'value' => $aVal,
                                        ]);

                                        if ($varOption) {
                                            $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                            $pVarValue->setItemVarOption($varOption);
                                            $pVarValue->setParent($entity);
                                            $pVarValue->setItemVar($pVar);
                                            $pVarValue->setValue($aVal);
                                            $this->persist($pVarValue);
                                        }
                                    } elseif (in_array($aVal, $removed)) {

                                        $children = $entity->getVarValuesText();
                                        if ($children->count()) {
                                            foreach($children as $child) {
                                                if ($child->getItemVar()->getId() != $pVar->getId()) {
                                                    continue;
                                                }

                                                if ($child->getValue() == $aVal) {
                                                    $this->remove($child);
                                                    break;
                                                }
                                            }
                                        }
                                    } // else, nothing changed
                                }

                                break;
                            case EntityConstants::INPUT_SELECT:
                                // assuming: single value, associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesText();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());

                                $varOption = $this->findOneBy(EntityConstants::ITEM_VAR_OPTION_TEXT, [
                                    'value' => $value,
                                ]);

                                if ($varOption) {
                                    $pVarValue->setItemVarOption($varOption);
                                    $pVarValue->setParent($entity);
                                    $pVarValue->setItemVar($pVar);
                                    $pVarValue->setValue($value);
                                    $this->persist($pVarValue);
                                }

                                break;
                            default:
                                // assuming: single value, not associated to option

                                $currentValue = isset($currentData[$key])
                                    ? $currentData[$key]
                                    : '';

                                if (!$currentValue && !$value) {
                                    continue;
                                }

                                if ($value == $currentValue) {
                                    continue;
                                }

                                $children = $entity->getVarValuesText();
                                if ($children->count()) {
                                    foreach($children as $child) {
                                        if ($child->getItemVar()->getId() != $pVar->getId()) {
                                            continue;
                                        }

                                        $this->remove($child);
                                    }
                                }

                                $pVarValue = $this->getVarValueInstance($objectType, $pVar->getDatatype());
                                $pVarValue->setParent($entity);
                                $pVarValue->setItemVar($pVar);
                                $pVarValue->setValue($value);
                                $this->persist($pVarValue);

                                break;
                        }

                        break;
                    default:

                        // don't save anything

                        break;
                }
            }
        }

        return $this;
    }

    /**
     * @param string $objectType Image Object Key
     * @param $parentEntity object|int Entity or Entity ID
     * @param array $images
     * @return $this
     */
    public function updateImages($objectType, $parentEntity, array $images)
    {
        if (is_int($parentEntity)) {
            $parentEntity = $this->find($objectType, $parentEntity);
        }

        // get images
        $currentImages = $parentEntity->getImages();
        if ($currentImages) {
            foreach($currentImages as $itemImage) {
                $found = false;
                foreach($images as $idx => $obj) {
                    if ($obj->id != $itemImage->getId()) {
                        continue;
                    }

                    // {"id":"1","sort_order":"1","is_default":false,"alt_text":""}
                    $itemImage
                        ->setSortOrder($obj->sort_order)
                        ->setIsDefault($obj->is_default)
                        ->setAltText($obj->alt_text);

                    $this->persist($itemImage);
                    unset($images[$idx]);
                    $found = true;
                    break;
                }

                // remove the image if it's not included
                if (!$found) {
                    $this->remove($itemImage);
                }
            }
        }

        if ($images) {
            foreach($images as $obj) {

                $itemImage = $this->find($objectType, $obj->id);

                // {"id":"1","sort_order":"1","is_default":false,"alt_text":""}
                $itemImage
                    ->setParent($parentEntity)
                    ->setSortOrder($obj->sort_order)
                    ->setIsDefault($obj->is_default)
                    ->setAltText($obj->alt_text);

                $this->persist($itemImage);
            }
        }

        return $this;
    }

    /**
     * Update Content Slots within a Content Entity
     *
     * @param $entity
     * @param array $slots
     * @return $this
     */
    public function updateContentSlots($entity, array $slots)
    {
        $objectType = EntityConstants::CONTENT_SLOT;
        if (is_int($entity)) {
            $entity = $this->find($objectType, $entity);
        }

        // get slots
        $currentSlots = $entity->getSlots();
        if ($currentSlots) {
            foreach($currentSlots as $contentSlot) {
                $found = false;
                foreach($slots as $idx => $data) {

                    if ($data['id'] != $contentSlot->getId()) {
                        continue;
                    }

                    $embedCode = isset($data['embed_code'])
                        ? $data['embed_code']
                        : '';

                    $title = isset($data['title'])
                        ? $data['title']
                        : '';

                    $bodyText = isset($data['body_text'])
                        ? $data['body_text']
                        : '';

                    $sortOrder = isset($data['sort_order'])
                        ? $data['sort_order']
                        : 1;

                    switch($data['content_type']) {
                        case EntityConstants::CONTENT_TYPE_IMAGE:

                            // update slot
                            $contentSlot
                                ->setParent($entity)
                                ->setContentType(EntityConstants::CONTENT_TYPE_IMAGE)
                                ->setTitle($title)
                                ->setBodyText($bodyText)
                                ->setSortOrder($sortOrder)
                                ->setEmbedCode('')
                            ;

                            if (isset($data['url'])) {
                                $contentSlot->setUrl($data['url']);
                            }

                            if (isset($data['path'])) {
                                $contentSlot->setPath($data['path']);
                            }

                            if (isset($data['alt_text'])) {
                                $contentSlot->setAltText($data['alt_text']);
                            }

                            break;
                        case EntityConstants::CONTENT_TYPE_EMBED:

                            // update slot
                            $contentSlot
                                ->setParent($entity)
                                ->setContentType(EntityConstants::CONTENT_TYPE_EMBED)
                                ->setTitle($title)
                                ->setBodyText($bodyText)
                                ->setSortOrder($sortOrder)
                                ->setAltText('')
                                ->setUrl('')
                                ->setEmbedCode($embedCode)
                                ->setPath('')
                            ;

                            break;
                        case EntityConstants::CONTENT_TYPE_HTML:

                            // update slot
                            $contentSlot
                                ->setParent($entity)
                                ->setContentType(EntityConstants::CONTENT_TYPE_HTML)
                                ->setTitle($title)
                                ->setBodyText($bodyText)
                                ->setSortOrder($sortOrder)
                                ->setAltText('')
                                ->setUrl('')
                                ->setEmbedCode('')
                                ->setPath('')
                            ;

                            break;
                        default:

                            break;
                    }

                    $this->persist($contentSlot);

                    unset($slots[$idx]);
                    $found = true;
                    break;
                }

                // remove the slot if it's not included
                if (!$found) {
                    $this->remove($contentSlot);
                }
            }
        }

        if ($slots) {
            foreach($slots as $data) {

                $contentSlot = $this->find($objectType, $data['id']);

                $embedCode = isset($data['embed_code'])
                    ? $data['embed_code']
                    : '';

                $title = isset($data['title'])
                    ? $data['title']
                    : '';

                $bodyText = isset($data['body_text'])
                    ? $data['body_text']
                    : '';

                $sortOrder = isset($data['sort_order'])
                    ? $data['sort_order']
                    : 1;

                switch($data['content_type']) {
                    case EntityConstants::CONTENT_TYPE_IMAGE:

                        // update slot
                        $contentSlot
                            ->setParent($entity)
                            ->setContentType(EntityConstants::CONTENT_TYPE_IMAGE)
                            ->setTitle($title)
                            ->setBodyText($bodyText)
                            ->setSortOrder($sortOrder)
                            ->setEmbedCode('')
                        ;

                        if (isset($data['url'])) {
                            $contentSlot->setUrl($data['url']);
                        }

                        if (isset($data['path'])) {
                            $contentSlot->setPath($data['path']);
                        }

                        if (isset($data['alt_text'])) {
                            $contentSlot->setAltText($data['alt_text']);
                        }

                        break;
                    case EntityConstants::CONTENT_TYPE_EMBED:

                        // update slot
                        $contentSlot
                            ->setParent($entity)
                            ->setContentType(EntityConstants::CONTENT_TYPE_EMBED)
                            ->setTitle($title)
                            ->setBodyText($bodyText)
                            ->setSortOrder($sortOrder)
                            ->setAltText('')
                            ->setUrl('')
                            ->setEmbedCode($embedCode)
                            ->setPath('')
                        ;

                        break;
                    case EntityConstants::CONTENT_TYPE_HTML:

                        // update slot
                        $contentSlot
                            ->setParent($entity)
                            ->setContentType(EntityConstants::CONTENT_TYPE_HTML)
                            ->setTitle($title)
                            ->setBodyText($bodyText)
                            ->setSortOrder($sortOrder)
                            ->setAltText('')
                            ->setUrl('')
                            ->setEmbedCode('')
                            ->setPath('')
                        ;

                        break;
                    default:

                        break;
                }

                $this->persist($contentSlot);
            }
        }

        return $this;
    }
}
