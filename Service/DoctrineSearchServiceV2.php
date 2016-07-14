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

use Doctrine\ORM\Query;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Repository\CartRepositoryInterface;

class DoctrineSearchServiceV2 extends AbstractSearchService
{

    protected $filtersSql = '';

    protected $mainSql = '';

    protected $countSql = '';

    protected $bindTypes = [];

    protected $filterParams = [];

    protected $advFilterParams = [];

    protected $facetFilterParams = [];

    /**
     * Over-riding, and forcing a like filter here
     *  If you change InnoDB tables to MyISAM
     *  for example, on the product table
     *  then run SQL in CoreBundle/Resources/sql/product_fulltext_add.sql
     *   and return parent::getSearchMethod() here
     *   the fulltext match() method will be enabled
     *
     * @return int|string
     */
    public function getSearchMethod()
    {
        // return parent::getSearchMethod();
        return CartRepositoryInterface::SEARCH_METHOD_LIKE;
    }

    protected function bindStatement(&$stmt, array $bindTypes, array $filterParams, array $advFilterParams, array $facetFilterParams)
    {
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

            if (is_array($this->getSearchField())) {
                if (count($this->getSearchField()) > 1) {
                    foreach($this->getSearchField() as $searchField) {
                        $bindType = $bindTypes[$x];
                        $z = $x + 1;
                        $stmt->bindValue($z, '%' . $this->sanitize($this->getQuery()) . '%', $bindType);
                        $x++;
                    }
                } else {

                    $bindType = $bindTypes[$x];
                    $z = $x + 1;
                    $stmt->bindValue($z, '%' . $this->sanitize($this->getQuery()) . '%', $bindType);
                    $x++;
                }
            } else {
                $bindType = $bindTypes[$x];
                $z = $x + 1;
                $stmt->bindValue($z, '%' . $this->sanitize($this->getQuery()) . '%', $bindType);
                $x++;
            }
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
                $z = $x + 1;
                $stmt->bindValue($z, $value, $bindType);
                $x++;

                $bindType = $bindTypes[$x];
                $z = $x + 1;
                $stmt->bindValue($z, $value, $bindType);
                $x++;
            }
        }
    }


    /**
     * Main execution of filters, advFilters, facetFilters
     *
     * @return array
     */
    protected function executeFilters()
    {
        if ($this->getExecutedFilters()) {
            return $this;
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

                                $rangeMin = (float) $rangeMin;
                                $rangeMax = (float) $rangeMax;

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

                        if (isset($filterInfo['join'])) {
                            $this->joins[] = $filterInfo['join'];
                            $field = $filterInfo['join']['table'] . ".{$field}";
                        } elseif (!is_int(strpos($field, '.'))) {
                            $field = "main.{$field}";
                        }

                        $whereConditions[] = "{$field} = ?";
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

        // note : use setFulltextIds() if you search somewhere else first eg SOLR / Elasticsearch
        if ($this->getFulltextIds()) {
            // ensure IDs are sanitized before you set them
            $whereConditions[] = "main.id in (" . implode(',', $this->getFulltextIds()) . ")";
        } else if ($this->getQuery()
            && $this->getSearchField()
            && $this->getSearchMethod()) {

            if (is_array($this->getSearchField())) {
                if (count($this->getSearchField()) > 1) {

                    $cond = '';
                    foreach($this->getSearchField() as $searchField) {
                        $bindTypes[$x] = \PDO::PARAM_STR;
                        if (!$cond) {
                            $cond .= "(main.{$searchField} like ?";
                        } else {
                            $cond .= " OR main.{$searchField} like ?";
                        }
                        $x++;
                    }
                    $cond .= ')';
                    $whereConditions[] = $cond;
                } else {

                    $fields = $this->getSearchField();
                    $searchField = $fields[0];

                    $bindTypes[$x] = \PDO::PARAM_STR;
                    $whereConditions[] = "main.{$searchField} like ?";
                    $x++;
                }
            } else {
                $bindTypes[$x] = \PDO::PARAM_STR;
                $whereConditions[] = "main.{$this->getSearchField()} like ?";
                $x++;
            }
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
                        $x++;

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
                        $whereConditions[] = "main.{$field} like ?";
                        break;
                    case 'starts':
                        $advFilterParams[] = $value . '%';
                        $whereConditions[] = "main.{$field} like ?";
                        break;
                    case 'ends':
                        $advFilterParams[] = '%'. $value;
                        $whereConditions[] = "main.{$field} like ?";
                        break;
                    case 'equals':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "main.{$field} = ?";
                        break;
                    case 'notequal':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "main.{$field} != ?";
                        break;
                    case 'null':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "main.{$field} IS NULL";
                        break;
                    case 'gt':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "main.{$field} > ?";
                        break;
                    case 'gte':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "main.{$field} >= ?";
                        break;
                    case 'lt':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "main.{$field} < ?";
                        break;
                    case 'lte':
                        $advFilterParams[] = $value;
                        $whereConditions[] = "main.{$field} <= ?";
                        break;
                    case 'in':
                        if (is_array($value)) {
                            $value = implode(',', $value); // CSV
                        }
                        $advFilterParams[] = '(' . $value . ')';
                        $whereConditions[] = "main.{$field} in ?";
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
            $whereConditions[] = "main.id in (select product_id from {$categoryTable} where category_id = ?)";
            $x++;
        }

        // handle stock, visibility filters with products


        // handle facet filters
        //  ie filters on EAV tables, child tables
        $facetFilterParams = [];
        if ($this->getFacetFilters()) {
            foreach($this->getFacetFilters() as $facetCode => $value) {

                $itemVar = $this->getVarByCode($facetCode);

                $tblValue = $objectType . '_' . EntityConstants::getVarDatatype($itemVar->getDatatype());
                $values = explode($this->valueSep, $value);
                $joinTbl = $tables[$itemVar->getDatatype()];
                $joinTblPre = 'ivo';

                if (count($values) > 1) {
                    $conditions = [];
                    foreach($values as $itemVarValue) {
                        $conditions[] = "({$pre}.value = ? OR {$joinTblPre}.url_value = ?)";
                        $facetFilterParams[] = $itemVarValue;

                        $bindTypes[$x] = $pdoBindTypes[$itemVar->getDatatype()];
                        $x++;

                        $bindTypes[$x] = $pdoBindTypes[$itemVar->getDatatype()];
                        $x++;

                    }
                    $dqlFilters[] = "({$pre}.item_var_id={$itemVar->getId()} AND (".implode(' OR ', $conditions)."))";
                } else {
                    $dqlFilters[] = "({$pre}.item_var_id={$itemVar->getId()} AND ({$pre}.value = ? OR {$joinTblPre}.url_value = ?))";
                    $facetFilterParams[] = $value;

                    $bindTypes[$x] = $pdoBindTypes[$itemVar->getDatatype()];
                    $x++;

                    $bindTypes[$x] = $pdoBindTypes[$itemVar->getDatatype()];
                    $x++;
                }

                $whereConditions[] = "main.id in (select parent_id from {$tblValue} {$pre} left join {$joinTbl} {$joinTblPre} on {$pre}.item_var_option_id={$joinTblPre}.id where ". implode(' AND ', $dqlFilters).")";
                $dqlFilters = [];

            }
        }

        $conditionsSql = implode(' AND ', $whereConditions);
        if (!$conditionsSql) {
            $conditionsSql = '1=1';
        }

        $groupSql = $this->getGroupBy()
            ? 'group by ' . implode(', ', $this->getGroupBy())
            : '';

        $colSql = '';
        if ($this->getColumns()) {
            $cols = [];
            foreach($this->getColumns() as $colData) {
                // add select
                $select = $colData['select'];
                $alias = $colData['alias'];
                if ($alias) {
                    $select .= " as {$alias}";
                }

                $cols[] = $select;
            }
            $colSql = ',' . implode(',', $cols);
        }

        $joinSql = '';
        if ($this->getJoins()) {
            $joins = [];
            foreach($this->getJoins() as $join) {
                $type = $join['type'];
                $table = $join['table'];
                $column = $join['column'];
                $joinAlias = $join['join_alias'];
                $joinColumn = $join['join_column'];
                $joins[] = "{$type} join {$table} on {$joinAlias}.{$joinColumn}={$table}.{$column}";
            }
            $joinSql = implode(' ', $joins);
        }

        $this->filtersSql = "select distinct(main.id) from {$mainTable} main {$joinSql} where {$conditionsSql}";
        $this->mainSql = "select distinct(main.id), main.* {$colSql} from {$mainTable} main {$joinSql} where {$conditionsSql} {$groupSql}";
        $this->countSql = "select count(distinct(main.id)) as count from {$mainTable} main {$joinSql} where {$conditionsSql}";
        $this->bindTypes = $bindTypes;
        $this->filterParams = $filterParams;
        $this->advFilterParams = $advFilterParams;
        $this->facetFilterParams = $facetFilterParams;

        $this->setExecutedFilters(true);
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

        if (!$this->getExecutedFilters()) {
            $this->executeFilters();
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

            $filtersStr = "vv.parent_id in (" . $this->filtersSql . ")";

            $sql = "SELECT distinct(vv.item_var_option_id), vv.item_var_id, ivo.value, ivo.url_value , iv.name, iv.code, iv.url_token, count(*) as count".
                " FROM `{$tblValue}` vv inner join `{$tblItemVarOption}` ivo on vv.item_var_option_id=ivo.id".
                " inner join `{$tblItemVar}` iv on vv.item_var_id=iv.id".
                " WHERE {$filtersStr}".
                " group by vv.item_var_option_id, vv.item_var_id".
                " order by `vv`.`item_var_id`, count desc";

            $em = $this->getEntityService()->getDoctrine()->getManager();
            $stmt = $em->getConnection()->prepare($sql);
            $this->bindStatement($stmt, $this->bindTypes, $this->filterParams, $this->advFilterParams, $this->facetFilterParams);
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
            $this->limit = (int) $limit;
            $this->page = (int) $page;
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

        // main filter execution
        //  sets $this->filteredIds
        $this->executeFilters();

        // optional : get facetCounts based on existing filters
        $facetCounts = $this->getEnableFacetCounts()
            ? $this->executeFacetCounts()
            : [];

        // execute count sql

        $countStmt = $em->getConnection()->prepare($this->countSql);
        $this->bindStatement($countStmt, $this->bindTypes, $this->filterParams, $this->advFilterParams, $this->facetFilterParams);
        $countStmt->execute();

        $countRow = $countStmt->fetch(\PDO::FETCH_ASSOC);
        $count = isset($countRow['count'])
            ? $countRow['count']
            : 0;

        // get main rows
        $mainSql = $this->mainSql;

        // sort
        if (isset($sortable[$this->getSortBy()])) {
            $mainSql .= " order by {$this->getSortBy()} {$this->getSortDir()}";
        }

        $mainSql .= " limit {$offset},{$this->getLimit()}";

        $entities = [];
        if ($count) {
            $mainStmt = $em->getConnection()->prepare($mainSql);
            $this->bindStatement($mainStmt, $this->bindTypes, $this->filterParams, $this->advFilterParams, $this->facetFilterParams);
            $mainStmt->execute();
            $entities = $mainStmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        if ($this->getPopulateVarValues()) {

            $this->getEntityService()
                ->populateVarValues($this->getObjectType(), $entities);
        }

        if (
            method_exists($repo, 'hasImages')
            && $repo->hasImages()
        ) {

            $this->getEntityService()
                ->populateImages($this->getObjectType(), $entities);
        }

        $this->result = [
            'facetCounts'  => $facetCounts,
            'facetFilters' => $this->getActiveFacetUrlData(), // active facets
            'entities'     => $entities,
            'total'        => $count,
            'pages'        => ceil($count / $this->getLimit()),
            'offset'       => $offset,
            //'searchQuery'  => $mainSql,
        ];

        return $this->getResult();
    }
}
