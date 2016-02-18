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

class MysqlEntityService
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
    public function getTableName($objectType)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getClassMetadata($this->repos[$objectType])
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
        $em = $this->getDoctrine()->getManager();
        if ($formData) {
            foreach($formData as $key => $value) {

                if (substr($key, 0, strlen('var_')) != 'var_') {
                    continue;
                }

                $info = explode('_', $key);
                $varId = isset($info[1]) ? $info[1] : 0;
                if (!$varId) {
                    continue;
                }

                /** @var ItemVar $pVar */
                $pVar = $this->getRepository(EntityConstants::ITEM_VAR)->find($varId);
                if (!$pVar) {
                    continue;
                }

                switch($pVar->getDatatype()) {
                    case EntityConstants::DATETIME:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::DATETIME);
                        break;
                    case EntityConstants::DECIMAL:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::DECIMAL);
                        $value = (double) $value;
                        break;
                    case EntityConstants::INT:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::INT);
                        $value = (int) $value;
                        break;
                    case EntityConstants::VARCHAR:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::VARCHAR);
                        break;
                    case EntityConstants::TEXT:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::TEXT);
                        break;
                    default:
                        $pVarValue = $this->getVarValueInstance($objectType, EntityConstants::VARCHAR);
                        break;
                }

                if (count($info) == 3 && $info[2] == 'option') {
                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    if (!count($value)) {
                        continue;
                    }

                    foreach($value as $aVal) {

                        switch($pVar->getDatatype()) {
                            case EntityConstants::DATETIME:
                                $varOption = $this->getRepository(EntityConstants::ITEM_VAR_OPTION_DATETIME)->find($aVal);
                                break;
                            case EntityConstants::DECIMAL:
                                $varOption = $this->getRepository(EntityConstants::ITEM_VAR_OPTION_DECIMAL)->find($aVal);
                                $value = (double) $value;
                                break;
                            case EntityConstants::INT:
                                $varOption = $this->getRepository(EntityConstants::ITEM_VAR_OPTION_INT)->find($aVal);
                                $value = (int) $value;
                                break;
                            case EntityConstants::VARCHAR:
                                $varOption = $this->getRepository(EntityConstants::ITEM_VAR_OPTION_VARCHAR)->find($aVal);
                                break;
                            case EntityConstants::TEXT:
                                $varOption = $this->getRepository(EntityConstants::ITEM_VAR_OPTION_TEXT)->find($aVal);
                                break;
                            default:
                                $varOption = $this->getRepository(EntityConstants::ITEM_VAR_OPTION_VARCHAR)->find($aVal);
                                break;
                        }

                        if (!$varOption) {
                            continue;
                        }

                        $pVarValue->setItemVarOption($varOption);
                        $value = $varOption->getValue();

                        $pVarValue->setParent($entity);
                        $pVarValue->setItemVar($pVar);
                        $pVarValue->setValue($value);
                        $em->persist($pVarValue);
                    }
                } else {
                    $pVarValue->setParent($entity);
                    $pVarValue->setItemVar($pVar);
                    $pVarValue->setValue($value);
                    $em->persist($pVarValue);
                }

            }
        }

        $em->flush();
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
        $em = $this->getDoctrine()->getManager();
        if ($formData) {
            foreach($formData as $key => $value) {
                if (substr($key, 0, strlen('var_')) != 'var_') {
                    continue;
                }

                $info = explode('_', $key);
                $itemVarId = isset($info[1]) ? $info[1] : 0;
                if (!$itemVarId) {
                    continue;
                }

                /** @var ItemVar $itemVar */
                $itemVar = $this->getRepository(EntityConstants::ITEM_VAR)->find($itemVarId);
                if (!$itemVar) {
                    continue;
                }

                $varOptionIds = [];
                $varOptions = $itemVar->getItemVarOptions();
                if ($varOptions) {
                    foreach($varOptions as $varOption) {
                        $varOptionIds[] = $varOption->getId();
                    }
                }

                // Figure out which vars were posted
                // Every var and value is already associated
                // If Var is MultiSelect (it has Options)
                // Else Var is single value, although SingleSelect has Options also

                //$entityClass = 'ItemVarValue' . ucfirst($itemVar->getDatatype());

                $objectRepo = $this->getVarValueRepository($objectType, $itemVar->getDatatype());

                if ($itemVar->getFormInput() == 'multiselect') {
                    // $value is an array of VarOption id's

                    if (!is_array($value)) {
                        throw new \Exception("Expected an Array. Problem with Mobile Cart's Item form handling/building.");
                    }

                    $prevVarOptionIds = [];

                    $objectVarValues = $objectRepo->findBy([
                        'parent'   => $entity,
                        'item_var' => $itemVar,
                    ]);

                    // current object var values
                    // remove any that weren't included
                    if ($objectVarValues) {
                        foreach($objectVarValues as $objectVarValue) {
                            $varOption = $objectVarValue->getItemVarOption();
                            if ($varOption) {
                                $varOptionId = $varOption->getId();
                                // collect IDs of current values
                                $prevVarOptionIds[] = $varOptionId;
                                if (!in_array($varOptionId, $value)) {
                                    $em->remove($objectVarValue);
                                }
                            }
                        }
                    }

                    if (!$value) {
                        continue;
                    }

                    foreach($value as $varOptionId) {

                        //skip existing value
                        if (in_array($varOptionId, $prevVarOptionIds)) {
                            continue;
                        }

                        $varOption = $this->getVarOptionRepository($itemVar->getDatatype())->find($varOptionId);
                        if (!$varOption) {
                            continue;
                        }

                        $itemVarValue = $this->getVarValueInstance($objectType, $itemVar->getDatatype());
                        $itemVarValue->setItemVarOption($varOption);
                        $itemVarValue->setParent($entity);
                        $itemVarValue->setItemVar($itemVar);
                        $itemVarValue->setValue($varOption->getValue());
                        $em->persist($itemVarValue);
                    }

                    // we are finished here. skip to next form field.
                    continue;
                } // no else, continue instead

                $itemVarValue = $this->getVarValueRepository($objectType, $itemVar->getDatatype())->findOneBy([
                    'parent'   => $entity,
                    'item_var' => $itemVar,
                ]);

                // input[var_X_option] indicates a ItemVarOption being used
                if (count($info) == 3 && $info[2] == 'option') {

                    $varOption = $this->getVarValueRepository($objectType, $itemVar->getDatatype())->find($value);
                    if (!$varOption) {
                        continue;
                    }

                    //ensure ItemVarOption is set to ItemVar
                    $itemVarValue->setItemVarOption($varOption);

                    //set value of ItemVar to be the same as ItemVarOption
                    $value = $varOption->getValue();
                }

                $itemVarValue->setParent($entity);
                $itemVarValue->setItemVar($itemVar);
                $itemVarValue->setValue($value);
                $em->persist($itemVarValue);
            }
        }

        $em->flush();
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
}
