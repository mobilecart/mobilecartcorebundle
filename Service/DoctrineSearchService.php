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

use Doctrine\ORM\Tools\Pagination\Paginator;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\CartRepositoryInterface;

class DoctrineSearchService extends AbstractSearchService
{
    /**
     * Over-riding, and forcing a like filter here
     *  If you change InnoDB tables to MyISAM
     *  for example, on the product table
     *  then run SQL in CoreBundle/Resources/sql/product_fulltext_add.sql
     *   and return parent::getSearchMethod() here
     *   the match() method will be enabled
     *
     * @return int|string
     */
    public function getSearchMethod()
    {
        // return parent::getSearchMethod();
        return CartRepositoryInterface::SEARCH_METHOD_LIKE;
    }

    /**
     * Main execution of filters, advFilters, facetFilters
     *
     * @return array
     */
    protected function executeFilters()
    {
        if ($this->getExecutedFilters()) {
            return $this->getFilteredIds();
        }

        if (!$this->getFacetFilters()
            && !$this->getFilters()
            && !$this->getAdvFilters()
            && !$this->getQuery()
            && !$this->getCategoryId()) {

            $this->setExecutedFilters(true);
            return [];
        }

        $objectType = $this->getObjectType();

        $whereConditions = [];

        $mainTable = $this->getEntityService()->getTableName($objectType);
        $pre = 'v2';
        $dqlFilters = [];
        $tables = $this->getEntityService()->getVarOptionTables();
        $bindTypes = [];
        $pdoBindTypes = $this->getEntityService()->getPdoBindTypes();
        $filterable = $this->getFilterable();

        $x = 0; //count($this->getFilters()); // for tracking bind types

        // handle basic filters eg key=value
        //  also, special handling of price range
        $filterParams = [];
        if ($this->getFilters()) {
            foreach($this->getFilters() as $field => $value) {
                foreach($filterable as $filterInfo) {
                    if ($field == $filterInfo['code']) {

                        // handle special case for numerical ranges
                        //  eg price=100-199 or subtotal=50-100
                        //  note : the handling of strpos is very intentional, want an index > 0
                        if ($filterInfo['type'] == 'number' && strpos($value, '-')) {
                            $rangeValues = explode('-', $value);
                            $rangeMin = $rangeValues[0];
                            $rangeMax = isset($rangeValues[1]) ? $rangeValues[1] : null;
                            if (isset($rangeMax)) {

                                $this->addAdvFilter([
                                    'field' => $field,
                                    'op' => 'gte',
                                    'value' => $rangeMin,
                                ]);

                                $this->addAdvFilter([
                                    'field' => $field,
                                    'op' => 'lt',
                                    'value' => $rangeMax,
                                ]);

                                break;
                            }
                        }

                        $whereConditions[] = "vv.{$field} = ?";
                        $filterParams[] = $value;

                        switch($filterInfo['type']) {
                            case 'boolean':
                                $bindTypes[$x] = \PDO::PARAM_INT;
                                break;
                            case 'number':
                                // todo : make this better
                                $bindTypes[$x] = \PDO::PARAM_INT;
                                break;
                            case 'string':
                                $bindTypes[$x] = \PDO::PARAM_STR;
                                break;
                            case 'date':
                                $bindTypes[$x] = \PDO::PARAM_STR;
                                break;
                            default:
                                $bindTypes[$x] = \PDO::PARAM_STR;
                                break;
                        }

                        $x++;
                        break;
                    }
                }
            }
        }

        // handle fulltext search first
        if ($this->getFulltextIds()) {
            // ensure IDs are sanitized before you set them
            $whereConditions[] = "vv.id in (" . implode(',', $this->getFulltextIds()) . ")";
        } else if ($this->getQuery()
            && $this->getSearchField()
            && $this->getSearchMethod()) {

            $bindTypes[$x] = \PDO::PARAM_STR;

            if ($this->getSearchMethod() == CartRepositoryInterface::SEARCH_METHOD_FULLTEXT) {
                // todo : handle postgres
                $whereConditions[] = "match(vv.{$this->getSearchField()}) against (? in boolean mode)";
            } else {
                $whereConditions[] = "vv.{$this->getSearchField()} like ?";
            }

            $x++;
        }

        // handle "advanced" filters
        //  eg filter_field[x], filter_op[x], filter_val[x]
        //  specifies a field, value, and operator ie (id > 100)
        $advFilterParams = [];
        if ($this->getAdvFilters()) {
            foreach($this->getAdvFilters() as $advFilter) {

                $field = $advFilter['field'];
                $op = $advFilter['op'];
                $value = $advFilter['value'];

                $found = false;
                foreach($filterable as $filterInfo) {
                    if ($field == $filterInfo['code']) {
                        $found = true;

                        switch($filterInfo['type']) {
                            case 'boolean':
                                $bindTypes[$x] = \PDO::PARAM_INT;
                                break;
                            case 'number':
                                // todo : make this better
                                $bindTypes[$x] = \PDO::PARAM_INT;
                                break;
                            case 'string':
                                $bindTypes[$x] = \PDO::PARAM_STR;
                                break;
                            case 'date':
                                $bindTypes[$x] = \PDO::PARAM_STR;
                                break;
                            default:
                                $bindTypes[$x] = \PDO::PARAM_STR;
                                break;
                        }

                        if ($found) {
                            $x++;
                        }

                        break;
                    }
                }

                if (!$found || !in_array($op, ['contains', 'starts', 'ends', 'equals', 'gt', 'gte', 'lt', 'lte', 'in'])) {
                    continue;
                }

                // example:
                //  $and->add($qb->expr()->eq('u.id', 1));

                switch($op) {
                    case 'contains':
                        $advFilterParams[] = '%'. $value . '%';
                        $whereConditions[] = "vv.{$field} like ?";
                        break;
                    case 'starts':
                        $advFilterParams[] = $value . '%';
                        $whereConditions[] = "vv.{$field} like ?";
                        break;
                    case 'ends':
                        $advFilterParams[] = '%'. $value;
                        $whereConditions[] = "vv.{$field} like ?";
                        break;
                    case 'equals':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "vv.{$field} = ?";
                        break;
                    case 'gt':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "vv.{$field} > ?";
                        break;
                    case 'gte':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "vv.{$field} >= ?";
                        break;
                    case 'lt':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "vv.{$field} < ?";
                        break;
                    case 'lte':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "vv.{$field} <= ?";
                        break;
                    case 'in':

                        // assumes value is CSV
                        $advFilterParams[] = '(' . $value . ')';
                        $whereConditions[] = "vv.{$field} in ?";

                        break;
                    default:

                        break;
                }
            }
        }

        // handle category filter with products
        if ($this->getCategoryId()
            && $this->getObjectType() == EntityConstants::PRODUCT) {

            $categoryTable = $this->getEntityService()->getTableName(EntityConstants::CATEGORY_PRODUCT);
            $bindTypes[$x] = \PDO::PARAM_INT;
            // todo : sometime in the future , add a category 'anchor', connecting multiple categories
            $whereConditions[] = "vv.id in (select product_id from {$categoryTable} where category_id = ?)";
            $x++;
        }

        // handle facet filters
        //  ie filters on EAV tables, child tables
        $facetFilterParams = [];
        if ($this->getFacetFilters()) {
            foreach($this->getFacetFilters() as $facetCode => $value) {

                $itemVar = $this->getVarByCode($facetCode);
                $bindTypes[$x] = $pdoBindTypes[$itemVar->getDatatype()];
                $tblValue = $objectType . '_' . EntityConstants::getVarDatatype($itemVar->getDatatype());
                $values = explode($this->valueSep, $value);
                $joinTbl = $tables[$itemVar->getDatatype()];
                $joinTblPre = 'ivo';

                if (count($values) > 1) {
                    $conditions = [];
                    foreach($values as $itemVarValue) {
                        $conditions[] = "({$pre}.value = ? OR {$joinTblPre}.url_value = ?)";
                        $facetFilterParams[] = $itemVarValue;
                    }
                    $dqlFilters[] = "({$pre}.item_var_id={$itemVar->getId()} AND (".implode(' OR ', $conditions)."))";
                } else {
                    $dqlFilters[] = "({$pre}.item_var_id={$itemVar->getId()} AND ({$pre}.value = ? OR {$joinTblPre}.url_value = ?))";
                    $facetFilterParams[] = $value;
                }

                $whereConditions[] = "vv.id in (select parent_id from {$tblValue} {$pre} left join {$joinTbl} {$joinTblPre} on {$pre}.item_var_option_id={$joinTblPre}.id where ". implode(' AND ', $dqlFilters).")";
                $dqlFilters = [];
                $x++;
            }
        }

        $conditionsSql = implode(' AND ', $whereConditions);
        $sql = "select distinct(vv.id) from {$mainTable} vv where {$conditionsSql}";

        $em = $this->getEntityService()
            ->getDoctrine()
            ->getManager();

        $stmt = $em->getConnection()->prepare($sql);

        $x = 0;

        // basic filters
        if ($filterParams) {
            foreach($filterParams as $value) {
                $bindType = $bindTypes[$x];
                $z = $x + 1;
                $stmt->bindValue($z, $value, $bindType);
                $x++;
            }
        }

        // search text filter
        if ($this->getQuery()
            && $this->getSearchField()
            && $this->getSearchMethod()) {

            $bindType = $bindTypes[$x];
            $z = $x + 1;

            if ($this->getSearchMethod() == CartRepositoryInterface::SEARCH_METHOD_FULLTEXT) {
                $stmt->bindValue($z, $this->sanitize($this->getQuery()), $bindType);
            } else {
                $stmt->bindValue($z, '%' . $this->sanitize($this->getQuery()) . '%', $bindType);
            }
            $x++;
        }

        // advanced filters
        if ($advFilterParams) {
            foreach($advFilterParams as $value) {
                $bindType = $bindTypes[$x];
                $z = $x + 1;
                $stmt->bindValue($z, $value, $bindType);
                $x++;
            }
        }

        // category filter(s)
        if ($this->getCategoryId()) {
            $bindType = $bindTypes[$x];
            $z = $x + 1;
            $stmt->bindValue($z, $this->getCategoryId(), $bindType);
            $x++;
        }

        // facet filters
        if ($facetFilterParams) {
            foreach($facetFilterParams as $i => $value) {
                $bindType = $bindTypes[$x];
                $z = $x + ($i * 2);
                $stmt->bindValue($z + 1, $value, $bindType);
                $stmt->bindValue($z + 2, $value, $bindType);
                $x++;
            }
        }

        $stmt->execute();
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $idx = 'id';
            $this->filteredIds[] = $row[$idx];
        }

        $this->setExecutedFilters(true);

        return $this->getFilteredIds();
    }

    /**
     * @return array
     */
    protected function executeFacetCounts()
    {
        if ($this->getExecutedFacetCounts()) {
            return $this->getFacetCounts();
        }

        $objectType = $this->getObjectType();
        $objectTypes = EntityConstants::getEavObjects();
        if (!isset($objectTypes[$objectType])) {
            return [];
        }

        // will use previously retrieved IDs if they exist
        $ids = $this->getExecutedFilters()
            ? $this->getFilteredIds()
            : $this->executeFilters();

        if (!$ids && $this->hasAnyFilters()) {
            $this->facetCounts = [];
            $this->setExecutedFacetCounts(true);
            return $this->facetCounts;
        }

        $facetCounts = [];
        $tables = $this->getEntityService()->getVarOptionTables();
        $tblItemVar = $this->getEntityService()->getTableName(EntityConstants::ITEM_VAR);

        // note: this cannot handle entity fields as facets, unless they are marked as a facet "somewhere"

        foreach(EntityConstants::getDatatypes() as $type => $label) {

            if (!in_array($type, $this->varDatatypes)) {
                continue;
            }

            $tblValue = $objectType . '_var_value_' . $type;
            $tblItemVarOption = $tables[$type];

            // execute main filters

            $filtersStr = $ids
                ? "vv.parent_id in (" . implode(',', $ids) . ")"
                : "1";

            $sql = "SELECT distinct(vv.item_var_option_id), vv.item_var_id, ivo.value, ivo.url_value , iv.name, iv.code, iv.url_token, count(*) as count".
                " FROM {$tblValue} vv inner join {$tblItemVarOption} ivo on vv.item_var_option_id=ivo.id".
                " inner join {$tblItemVar} iv on vv.item_var_id=iv.id and iv.is_facet=1".
                " WHERE {$filtersStr} group by vv.item_var_option_id, vv.item_var_id".
                " order by vv.item_var_id, count desc";

            $em = $this->getEntityService()->getDoctrine()->getManager();
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->execute();

            $currentCode = '';
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $code = $row['code'];
                $facetName = $row['name'];
                $facetValue = $row['value'];
                $urlValue = $row['url_value'];
                $count = $row['count'];
                $urlToken = $row['url_token'];
                $isActive = isset($facetFilters[$code]) ? 1 : 0;

                if ($code != $currentCode) {

                    $facetCounts[$code] = [
                        'terms'    => [],
                        'label'    => $facetName,
                        'urlToken' => $urlToken,
                        'isActive' => $isActive,
                    ];

                    $currentCode = $code;
                }

                $facetCounts[$code]['terms'][] = [
                    'term'       => $facetValue,
                    'urlToken'   => $urlToken,
                    'urlValue'   => $urlValue,
                    'url'        => '', // populated later in populateFacetLinks()
                    'count'      => $count,
                    'remove_url' => '', // populated later in populateFacetLinks()
                ];
            }
        }

        $this->facetCounts = $facetCounts;
        $this->populateFacetLinks();
        $this->setExecutedFacetCounts(true);
        return $this->facetCounts;
    }

    /**
     * @param array $params
     * @return array|mixed
     */
    public function search(array $params = [])
    {
        if ($this->getExecutedFilters()) {
            return $this->getResult();
        }

        // todo: add sql filter strings, next to filteredIds[]

        if ($params) {

            // fulltext search
            $search = isset($params['search']) ? $params['search'] : '';
            // facets included in facet counts, result
            $facets = isset($params['facets']) ? $params['facets'] : [];
            // filter[key] = value
            $filters = isset($params['filters']) ? $params['filters'] : [];
            // advFilter['field' => 'a', 'op' => 'b', 'value' => 'c']
            $advFilters = isset($params['filters']) ? $params['filters'] : [];
            // page number
            $page = (int) isset($params['page']) ? $params['page'] : 1;
            // limit per page
            $limit = isset($params['limit']) ? $params['limit'] : 15;
            if ($limit < 1) {
                $limit = 1;
            }
            // field to sort by
            $sortBy = isset($params['sort_by']) ? $params['sort_by'] : '';
            // sort direction
            $sortDir = isset($params['sort_dir']) ? $params['sort_dir'] : '';

            $this->sortDir = $sortDir;
            $this->sortBy = $sortBy;
            $this->limit = $limit;
            $this->page = $page;
            $this->filters = $filters;
            $this->advFilters = $advFilters;
            $this->facets = $facets;

            $repo = $this->getEntityService()->getRepository($this->getObjectType());
            $sortable = $repo->getSortableFields();
            $filterable = $repo->getFilterableFields();
            $this->sortable = $sortable;
            $this->filterable = $filterable;
            $this->query = $search;
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getEntityService()->getDoctrine()->getManager();
        $repoStr = $this->getObjectRepository($this->getObjectType());
        $repo = $this->getEntityService()->getRepository($this->getObjectType());
        $sortable = $repo->getSortableFields();
        $offset = ($this->getPage() - 1) * $this->getLimit();

        /*
         * Note:
         * Joining tables here in any way
         * will probably break the ability to use the paginator
         * since Doctrine complains it cannot count() on joined tables
         */

        $qb = $em->createQueryBuilder();
        $qb->select('i')->from($repoStr, 'i');
        $and = $qb->expr()->andX();

        // main filter execution
        //  sets $this->filteredIds
        $this->executeFilters();

        // optional : get facetCounts based on existing filters
        $facetCounts = $this->getEnableFacetCounts()
            ? $this->executeFacetCounts()
            : [];

        if ($this->getExecutedFilters()
            && $this->hasAnyFilters()) {

            if ($this->getFilteredIds()) {
                $and->add($qb->expr()->in('i', $this->getFilteredIds()));
            } else {
                $and->add($qb->expr()->in('i', [0]));
            }

            $qb->add('where', $and);
        } else {
            $qb->add('where', '1=1');
        }

        // sort
        if (isset($sortable[$this->getSortBy()])) {
            $qb->addOrderBy("i.{$this->getSortBy()}", $this->getSortDir());
        }

        $qb->setFirstResult($offset)
            ->setMaxResults($this->getLimit());

        $entities = [];

        $paginator = new Paginator($qb, $fetchJoinCollection = true);
        $count = $paginator->count();
        if ($count) {
            foreach($paginator as $entity) {
                $entities[] = $entity->getBaseData();
            }
        }

        if ($this->getPopulateVarValues()) {

            $this->getEntityService()
                ->populateVarValues($this->getObjectType(), $entities);

        }

        $this->result = [
            'facetCounts'  => $facetCounts,
            'facetFilters' => $this->getActiveFacetUrlData(), // active facets
            'entities'     => $entities,
            'total'        => $count,
            'pages'        => ceil($count / $this->getLimit()),
            'offset'       => $offset,
            //'searchQuery'  => $qb->getDQL(),
        ];

        return $this->getResult();
    }
}
