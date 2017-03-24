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
     * @param string $column
     * @param string $addtl
     * @param string $newIdx
     * @return $this
     */
    public function populateData($objectType, &$values, $column = 'parent_id', $addtl = '', $newIdx = '')
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
            if ($addtl) {
                $sql .= " {$addtl}";
            }

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
     * Update EAV for a single entity
     *
     * @param $entity
     * @param array $data
     * @return $this
     */
    public function persistVariants($entity, array $data)
    {
        // nothing to do if there's no data
        if (!$data) {
            return $this;
        }

        $objectType = $entity->getObjectTypeKey();

        // separate base data from variant data
        $instance = $this->getInstance($objectType);
        $baseDataKeys = array_keys($instance->getBaseData());

        // check if we have multi-select inputs before we getVarValues(), which executes queries
        $hasMultiSelect = false;
        foreach($data as $k => $v) {
            if (is_array($v)) {
                $hasMultiSelect = true;
                break;
            }
        }

        // Note : if there's no value selected in a select input
        //  it won't show up in the post data
        // You need to detect that yourself and pass in an empty array for that key; if you want the values deleted
        if ($hasMultiSelect) {
            $currentValues = $entity->getVarValues();
            if ($currentValues) {
                foreach($currentValues as $varValue) {
                    $itemVar = $varValue->getItemVar();
                    $code = $itemVar->getCode();
                    $value = $varValue->getValue();

                    if (isset($data[$code])
                        && is_array($data[$code])
                        && (!count($data[$code]) || !in_array($value, $data[$code]))
                    ) {
                        $this->remove($varValue);
                    }
                }
            }
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

            $varValueObjectType = $this->getVarValueKey($objectType, $itemVar->getDatatype());

            $varOptionObjectType = '';
            switch($itemVar->getDatatype()) {
                case EntityConstants::DATETIME:
                    $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_DATETIME;
                    break;
                case EntityConstants::DECIMAL:
                    $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_DECIMAL;
                    break;
                case EntityConstants::INT:
                    $varOptionObjectType = EntityConstants::ITEM_VAR_OPTION_INT;
                    $v = (int) $v;
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

            // most simple way to separate the logic is on the form input
            // its either single-option, multi-option, or single value
            switch($itemVar->getFormInput()) {
                case EntityConstants::INPUT_SELECT:

                    // retrieve existing value, and delete if it exists
                    $aVarValues = $this->findBy($varValueObjectType, [
                        'parent' => $entity,
                        'item_var' => $itemVar->getId(),
                    ]);

                    if ($aVarValues) {
                        foreach($aVarValues as $aVarValue) {
                            $this->remove($aVarValue);
                        }
                    }

                    $varOption = $this->findOneBy($varOptionObjectType, [
                        'item_var' => $itemVar->getId(),
                        'value' => $v
                    ]);

                    $varOptionId = $varOption
                        ? $varOption->getId()
                        : 0;

                    if (!$varOption) {

                        // save new var option
                        $varOption = $this->getInstance($varOptionObjectType);
                        $varOption->setItemVar($itemVar)
                            ->setValue($v)
                            ->setUrlValue($this->slugify($v));

                        $this->persist($varOption);

                        // insert new row in x_var_value_y

                        $varValue = $this->getInstance($varValueObjectType);
                        $varValue->setItemVar($itemVar)
                            ->setItemVarOption($varOption)
                            ->setParent($entity)
                            ->setValue($v);

                    } else {

                        // we have a ItemVarOption

                        // look for for existing VarValue in x_var_value_y
                        //  we can only have 1 value, since it's not a multi-select

                        $varValue = $this->findOneBy($varValueObjectType, [
                            'parent' => $entity->getId(),
                            'item_var' => $itemVar->getId(),
                            'item_var_option' => $varOptionId,
                        ]);

                        if (!$varValue) {

                            $varValue = $this->getInstance($varValueObjectType);
                            $varValue->setItemVar($itemVar)
                                ->setParent($entity)
                                ->setItemVarOption($varOption);
                        }

                        $varValue->setValue($v);
                        $this->persist($varValue);
                    }

                    break;
                case EntityConstants::INPUT_MULTISELECT:

                    // load all values for this entity and variant
                    $varValues = $this->findBy($varValueObjectType, [
                        'parent' => $entity->getId(),
                        'item_var' => $itemVar->getId(),
                    ]);

                    if (!is_array($v)) {
                        $v = [$v];
                    }

                    // loop on form data
                    foreach($v as $varValue) {

                        // see if we have a VarOption with the specified value
                        $varOption = $this->findOneBy($varOptionObjectType, [
                            'item_var' => $itemVar->getId(),
                            'value' => $varValue
                        ]);

                        // if we dont have an Option, create one
                        if (!$varOption) {

                            // automatically create new var option
                            $varOption = $this->getInstance($varOptionObjectType);
                            $varOption->setItemVar($itemVar)
                                ->setValue($v)
                                ->setUrlValue($this->slugify($v));

                            $this->persist($varOption);

                            // insert new row in x_var_value_y

                            $aVarValue = $this->getInstance($varValueObjectType);
                            $aVarValue->setItemVar($itemVar)
                                ->setItemVarOption($varOption)
                                ->setParent($entity)
                                ->setValue($varValue);

                            $this->persist($aVarValue);

                        } else {

                            // we have a VarOption

                            // figure out if we already have this value, and avoid a duplicate being saved
                            $exists = false;
                            if ($varValues) {
                                foreach($varValues as $k => $aVarValue) {
                                    if ($aVarValue->getValue() == $varValue) {
                                        unset($varValues[$k]); // unset values as we find them. whatever is left is deleted
                                        $exists = true;
                                        break;
                                    }
                                }
                            }

                            if ($exists) {
                                // we already have the value, nothing to do
                                continue;
                            }

                            // assuming we need to create a VarValue

                            $aVarValue = $this->getInstance($varValueObjectType);
                            $aVarValue->setItemVar($itemVar)
                                ->setParent($entity)
                                ->setItemVarOption($varOption)
                                ->setValue($varValue);

                            $this->persist($aVarValue);
                        }
                    }

                    break;
                default:

                    // look for for row
                    $varValue = $this->findOneBy($varValueObjectType, [
                        'parent' => $entity->getId(),
                        'item_var' => $itemVar->getId(),
                    ]);

                    if (!$varValue) {
                        $varValue = $this->getInstance($varValueObjectType);
                        $varValue->setItemVar($itemVar)
                            ->setParent($entity);
                    }

                    $varValue->setValue($v);
                    $this->persist($varValue);

                    break;
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
