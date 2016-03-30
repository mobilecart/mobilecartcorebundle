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

use Symfony\Component\HttpFoundation\Request;
use MobileCart\CoreBundle\Constants\EntityConstants;

abstract class AbstractSearchService
{

    /**
     * Note:
     *  - some members are public for json-encoding this object
     *  - the result is standardized on the same result returned from ElasticSearch
     */

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $result = [];

    /**
     * Response format
     *
     * @var string
     */
    protected $format = 'html';

    /**
     * @var string
     */
    protected $objectType;

    /**
     * Is objectType EAV-based
     *
     * @var bool
     */
    protected $isEAV = false;

    /**
     * Flag for whether to pull facet counts or not
     *  can reduce ~5 queries
     *
     * @var bool
     */
    protected $enableFacetCounts = true;

    /**
     * @var string
     */
    protected $facetPrefix = '';

    /**
     * Facets used in Search
     *
     * r[$var->getName()] = $prefix . $var->getCode()
     *
     * @var array
     */
    protected $facets = [];

    /**
     * Associative array of URL Token => facetCode
     * r[$var->getUrlToken()] = $this->getFacetPrefix() . $var->getCode()
     *
     * @var array
     */
    protected $facetTokens = [];

    /**
     * Flag for marking the search filters as executed
     *  this helps when filteredIds is empty
     *
     * @var bool
     */
    protected $executedFilters = false;

    /**
     * Flag for marking the facetCounts as executed
     *  this helps when facetCounts is empty
     *
     * @var bool
     */
    protected $executedFacetCounts = false;

    /**
     * IDs after executing all filters
     *
     * @var array
     */
    protected $filteredIds = [];

    /**
     * Optional:
     *  IDs which were retrieved from an external source
     *   sub-set of filteredIds
     *
     * @var array
     */
    protected $fulltextIds = [];

    /**
     * Array of Facet objects (and their individual terms)
     *  r[] = Facet object
     *
     * @var array
     */
    protected $facetCounts = [];

    /**
     * Map of array indexes
     *  i.e. ElasticSearch prefixes its data
     *
     * r[$prefix . $var->getCode()] = $var->getCode()
     *
     * @var array
     */
    protected $selectInputVars = [];

    /**
     * Flag for coordinating with controller
     *  on handling downloads
     *
     * @var bool
     */
    protected $isDownload = false;

    /**
     * @var string
     */
    protected $isDownloadParam = 'download';

    /**
     * @var mixed
     */
    protected $entityService;

    /**
     * @var string
     */
    protected $formatParam = 'format';

    /**
     * @var array
     */
    protected $formats = [
        'html' => 'HTML',
        'xml'  => 'XML',
        'json' => 'JSON',
        'csv'  => 'CSV',
    ];

    /**
     * Category ID parameter
     *
     * @var string
     */
    public $categoryIdParam = 'cat_id';

    /**
     * Mostly for products
     *
     * Category ID filter
     *  defaults to single integer
     *   but can also be CSV string, or array of integers
     *
     * @var int|array|string
     */
    protected $categoryId;

    /**
     * ItemVar entities
     *
     * @var array
     */
    protected $vars = [];

    /**
     * Datatypes of ItemVars
     *
     * @var array
     */
    protected $varDatatypes = [];

    /**
     * Basic filters, mostly for properties of entities
     *  eg is_new=1, is_on_sale=1, etc
     *  for including with advFilters
     *
     * @var array
     */
    public $filters = [];

    /**
     * Basic filters, for URL values of facets/ItemVars
     *  r[$var->getCode()] = "value from URL"
     *
     * @var array
     */
    protected $facetFilters = [];

    /**
     * Advanced filters, mostly for admin
     *  allows specification of filter operation:
     *   starts with, contains, ends with, greater than, etc
     *   r[] = array("field" => '', "op" => '', "value" => '', "type" => '')
     *
     * @var array
     */
    public $advFilters = [];

    /**
     * Fulltext Search parameter
     *
     * @var string
     */
    public $queryParam = 'q';

    /**
     * Fulltext Search value
     *
     * @var string
     */
    protected $query = '';

    /**
     * Page number parameter
     *
     * @var string
     */
    public $pageParam = 'page';

    /**
     * Page number value
     *
     * @var int
     */
    public $page = 1;

    /**
     * Page size parameter
     *
     * @var string
     */
    public $limitParam = 'per_page';

    /**
     * Page size value
     *
     * @var int
     */
    public $limit = 15;

    /**
     * Sort-by parameter
     *
     * @var string
     */
    public $sortByParam = 'sort';

    /**
     * Sort-by value
     *
     * @var string
     */
    public $sortBy = '';

    /**
     * Sort direction parameter
     *
     * @var string
     */
    public $sortDirParam = 'direction';

    /**
     * Sort direction value
     *
     * @var string
     */
    public $sortDir = '';

    /**
     * Fields which are sortable for the
     *  specified object type
     *
     * @var array
     */
    public $sortable = [];

    /**
     * Fields which are filterable for the
     *  specified object type
     *
     * @var array
     */
    public $filterable = [];

    /**
     * Field which is searchable
     *  for the specified object type
     *
     * @var string
     */
    public $searchField = '';

    /**
     * Method for searching the searchField
     *  eg LIKE or FullText
     *  see CartRepositoryInterface
     *
     * @var string
     */
    public $searchMethod = '';

    /**
     * For reference, mostly un-used
     *
     * @var array
     */
    public $filterOps = [
        'equals' => 'Equals',
        'starts' => 'Starts With',
        'ends' => 'Ends With',
        'gt' => 'Greater Than',
        'gte' => 'Greater Than or Equal',
        'lt' => 'Less Than',
        'lte' => 'Less Than or Equal',
        'contains' => 'Contains',
        'in' => 'In List',
    ];

    /**
     * Filter operators and associated data types
     *
     * @var array
     */
    public $advFilterOps = [
        [
            'code' => 'equals',
            'label' => 'Equals',
            'types' => [
                'number',
                'string',
                'date',
                'boolean',
            ],
        ],
        [
            'code' => 'starts',
            'label' => 'Starts With',
            'types' => [
                'string',
            ],
        ],
        [
            'code' => 'ends',
            'label' => 'Ends With',
            'types' => [
                'string',
            ],
        ],
        [
            'code' => 'contains',
            'label' => 'Contains',
            'types' => [
                'string',
            ],
        ],
        [
            'code' => 'gt',
            'label' => 'Greater Than',
            'types' => [
                'number',
                'date',
            ],
        ],
        [
            'code' => 'gte',
            'label' => 'Greater Than or Equal',
            'types' => [
                'number',
                'date',
            ],
        ],
        [
            'code' => 'lt',
            'label' => 'Less Than',
            'types' => [
                'number',
                'date',
            ],
        ],
        [
            'code' => 'lte',
            'label' => 'Less Than or Equal',
            'types' => [
                'number',
                'date',
            ],
        ],
        [
            'code' => 'in',
            'label' => 'In List',
            'types' => [
                'number',
                //'string', // todo: implement this
            ],
        ],
    ];

    /**
     * Display mode parameter
     *
     * @var string
     */
    protected $modeParam = 'mode'; // grid, rows, etc

    /**
     * Display mode parameter value
     *
     * @var string
     */
    protected $mode;

    /**
     * Token for detecting multi-value parameter values
     *
     * @var string
     */
    public $valueSep = ',';

    /**
     * For RDBMS only:
     * Flag for enabling/disabling loading all var values
     *  it reduces 5 queries if you disable it
     *
     * @var bool
     */
    protected $populateVarValues = true;

    public function __construct()
    {

    }

    /**
     * @param $str
     * @return mixed
     */
    public function sanitize($str)
    {
        $str = str_replace("'", '', $str);
        $str = str_replace('"', '', $str);
        $str = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $str)));
        $str = str_replace('--', '-', $str);
        $str = str_replace('-', ' ', $str);
        return $str;
    }

    /**
     * Set HTTP Request
     * This is required for creating Facet add/remove URLs
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get HTTP Request
     *
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return bool
     */
    public function hasAnyFilters()
    {
        return (
            count($this->getFilters())
            || count($this->getAdvFilters())
            || count($this->getFacetFilters())
            || ($this->getCategoryId() > 0)
            || strlen($this->getQuery())
        );
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setPopulateVarValues($yesNo)
    {
        $this->populateVarValues = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPopulateVarValues()
    {
        return $this->populateVarValues;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setFulltextIds(array $ids)
    {
        $this->fulltextIds = $ids;
        return $this;
    }

    /**
     * @return array
     */
    public function getFulltextIds()
    {
        return $this->fulltextIds;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setExecutedFilters($yesNo)
    {
        $this->executedFilters = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getExecutedFilters()
    {
        return $this->executedFilters;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setExecutedFacetCounts($yesNo)
    {
        $this->executedFacetCounts = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getExecutedFacetCounts()
    {
        return $this->executedFacetCounts;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setFilteredIds(array $ids)
    {
        $this->filteredIds = $ids;
        return $this;
    }

    /**
     * IDs which are set after filters are executed
     *
     * @return array
     */
    public function getFilteredIds()
    {
        return $this->filteredIds;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $objectType
     * @return string
     */
    public function getObjectRepository($objectType)
    {
        return $this->getEntityService()->getObjectRepository($objectType);
    }

    /**
     * @param $objectType
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setObjectType($objectType)
    {
        if (!$this->getEntityService()->getObjectRepository($objectType)) {
            throw new \InvalidArgumentException("Object type: {$objectType} is not valid");
        }
        $this->objectType = $objectType;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsEAV($yesNo)
    {
        $this->isEAV = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEAV()
    {
        return $this->isEAV;
    }

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * Set Display mode eg grid, rows, etc
     *  mostly for reference in the View layer
     *
     * @param $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Get Display mode
     *  mostly for reference in the View layer
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableFacetCounts($yesNo)
    {
        $this->enableFacetCounts = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableFacetCounts()
    {
        return $this->enableFacetCounts;
    }

    /**
     * @param array $facetCounts
     * @return $this
     */
    public function setFacetCounts(array $facetCounts)
    {
        $this->facetCounts = $facetCounts;
        return $this;
    }

    /**
     * @return array
     */
    public function getFacetCounts()
    {
        return $this->facetCounts;
    }

    /**
     * Additional handling of data
     *  i.e. ElasticSearch prefixes columns for facets, etc
     *
     * @param $prefix
     * @return $this
     */
    public function setFacetPrefix($prefix)
    {
        $this->facetPrefix = $prefix;
        return $this;
    }

    /**
     * Additional handling of data
     *  i.e. ElasticSearch prefixes columns for facets, etc
     *
     * @return string
     */
    public function getFacetPrefix()
    {
        return $this->facetPrefix;
    }

    /**
     * Specify a facet for the returned search result
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addFacet($key, $value)
    {
        $this->facets[$key] = $value;
        return $this;
    }

    /**
     * Remove a specified facet for the the returned search result
     *
     * @param $key
     * @return $this
     */
    public function removeFacet($key)
    {
        if (isset($this->facets[$key])) {
            unset($this->facets[$key]);
        }
        return $this;
    }

    /**
     * Get the specified facets being requested
     *  to be included in the search result
     *
     * @return array
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * Specify a filter value on a facet field
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addFacetFilter($key, $value)
    {
        $this->facetFilters[$key] = $value;
        return $this;
    }

    /**
     * Remove a filter value on a facet field
     *
     * @param $key
     * @return $this
     */
    public function removeFacetFilter($key)
    {
        if (isset($this->facetFilters[$key])) {
            unset($this->facetFilters[$key]);
        }
        return $this;
    }

    /**
     * Get the specified filter values and associated facet fields
     *
     * @return array
     */
    public function getFacetFilters()
    {
        return $this->facetFilters;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addFilter($key, $value)
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function addFilters(array $filters)
    {
        if (!$filters) {
            return $this;
        }

        foreach($filters as $k => $v) {
            $this->addFilter($k, $v);
        }

        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function removeFilter($key)
    {
        if (isset($this->filters[$key])) {
            unset($this->filters[$key]);
        }

        return $this;
    }

    /**
     * Basic filters
     *  filters[property] = value
     *   intended mostly for properties of entities eg product.is_new
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get relevant data about the specified filters and facets
     *  for/from the search result
     *
     * @return array
     */
    public function getActiveFacetUrlData()
    {
        $facetCounts = $this->getFacetCounts();

        $data = [];
        // r[$var->getCode()] = "value from URL"
        if ($this->facetFilters) {
            foreach($this->facetFilters as $code => $value) {
                $var = $this->getVarByCode($code);
                $count = '';
                $found = false;
                foreach($facetCounts as $i => $termsData) {
                    $terms = $termsData['terms'];
                    if (!$terms) { continue; }
                    foreach($terms as $j => $termData) {
                        if ($value == $facetCounts[$i]['terms'][$j]['term']) {
                            $count = $facetCounts[$i]['terms'][$j]['count'];
                        }
                    }
                    if ($found) { break; }
                }

                $data[] = [
                    'term' => $value,
                    'count' => $count,
                    'code' => $code,
                    'remove_url' => $this->getRemoveFacetTermUrl($code, $value),
                    'label' => $var->getName(),
                    'url_token' => $var->getUrlToken(),
                ];
            }
        }

        return $data;
    }

    /**
     * Get URL token by Facet Code
     *
     * @param string $facetCode
     * @return string|bool
     */
    public function getUrlTokenByFacetCode($facetCode)
    {
        if (!$this->facetTokens) {
            return false;
        }

        $tokens = array_flip($this->facetTokens);
        return isset($tokens[$facetCode])
            ? $tokens[$facetCode]
            : false;
    }

    /**
     * Get Facet Code By URL Token, if one exists
     *
     * @param string $urlToken
     * @return string|bool
     */
    public function getFacetCodeByUrlToken($urlToken)
    {
        return isset($this->facetTokens[$urlToken])
            ? $this->facetTokens[$urlToken]
            : false;
    }

    /**
     * @return array
     */
    public function getAdvFilters()
    {
        return $this->advFilters;
    }

    /**
     * @param array $advFilter
     * @return $this
     */
    public function addAdvFilter(array $advFilter)
    {
        $this->advFilters[] = $advFilter;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilterOps()
    {
        return $this->filterOps;
    }

    /**
     * @return array
     */
    public function getAdvFilterOps()
    {
        return $this->advFilterOps;
    }

    /**
     * @param $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * Set fulltext search string
     *
     * @param $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Get fulltext search string
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getQueryParam()
    {
        return $this->queryParam;
    }

    /**
     * @param $sortBy
     * @return $this
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    /**
     * Get sort-by parameter value
     *
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param $sortByParam
     * @return $this
     */
    public function setSortByParam($sortByParam)
    {
        $this->sortByParam = $sortByParam;
        return $this;
    }

    /**
     * Get sort-by parameter
     *
     * @return string
     */
    public function getSortByParam()
    {
        return $this->sortByParam;
    }

    /**
     * Set sort direction parameter value
     *
     * @param $sortDir
     * @return $this
     */
    public function setSortDir($sortDir)
    {
        $this->sortDir = $sortDir;
        return $this;
    }

    /**
     * Get sort direction parameter value
     *
     * @return string
     */
    public function getSortDir()
    {
        return $this->sortDir;
    }

    /**
     * Set sort direction parameter
     *
     * @param $sortDirParam
     * @return $this
     */
    public function setSortDirParam($sortDirParam)
    {
        $this->sortDirParam = $sortDirParam;
        return $this;
    }

    /**
     * Get sort direction parameter
     *
     * @return string
     */
    public function getSortDirParam()
    {
        return $this->sortDirParam;
    }

    /**
     * @param array $filterable
     * @return $this
     */
    public function setFilterable(array $filterable)
    {
        $this->filterable = $filterable;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilterable()
    {
        return $this->filterable;
    }

    /**
     * @param array $sortable
     * @return $this
     */
    public function setSortable(array $sortable)
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * @return array
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Set page number
     *
     * @param $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = (int) $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return (int) ($this->page)
            ? $this->page
            : 1;
    }

    /**
     * @return string
     */
    public function getPageParam()
    {
        return $this->pageParam;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @return string
     */
    public function getLimitParam()
    {
        return $this->limitParam;
    }

    /**
     * @param $field
     * @return $this
     */
    public function setSearchField($field)
    {
        $this->searchField = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchField()
    {
        return $this->searchField;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setSearchMethod($method)
    {
        $this->searchMethod = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchMethod()
    {
        return $this->searchMethod;
    }

    /**
     * Set Category filter
     *
     * @param int $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = (int) $categoryId;
        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Parse Request , set paginator parameters
     *
     * @param Request $request
     * @return $this
     */
    public function parseRequest(Request $request)
    {
        $this->setRequest($request);

        $format = $request->get($this->formatParam, 'html');
        $formats = $this->getFormats();

        if (isset($formats[$format])) {
            $this->setFormat($format);
        }

        $filterFields = $request->get('filter_field', []);
        $filterOps = $request->get('filter_op', []);
        $filterVals = $request->get('filter_val', []);

        $categoryId = $this->getRequest()->get($this->categoryIdParam, '');
        $this->query = $this->getRequest()->get($this->queryParam, '');
        $this->page = $this->getRequest()->get($this->pageParam, 1);
        $this->limit = $this->getRequest()->get($this->limitParam, 30);
        $this->sortBy = $this->getRequest()->get($this->sortByParam, '');
        $this->sortDir = $this->getRequest()->get($this->sortDirParam, '');
        if ($this->sortDir != 'desc') {
            $this->sortDir = 'asc';
        }

        // set sortable info
        // assuming method retrieves repository by key/object type
        $repo = $this->getEntityService()->getRepository($this->getObjectType());
        $sortable = $repo->getSortableFields();
        $filterable = $repo->getFilterableFields();
        $this->setIsEAV($repo->isEAV());
        $this->searchField = $repo->getSearchField();
        $this->searchMethod = $repo->getSearchMethod();

        // only need the ID in some cases
        // categoryId may already be set
        if ($categoryId) {
            $this->setCategoryId($categoryId);
            //$this->addFacetFilter('category_ids', $categoryId);
        }

        if ($this->getIsEAV()) {
            // build facet filters
            $vars = $this->getVars();
            $varDatatypes = $this->getVarDatatypes();
            if ($vars) {
                foreach($vars as $var) {

                    if (!in_array($var->getFormInput(), ['select', 'multiselect'])) {
                        continue;
                    }

                    // skip, if there isn't a url token specified
                    if (!$var->getUrlToken()) {
                        continue;
                    }

                    $this->facetTokens[$var->getUrlToken()] = $this->getFacetPrefix() . $var->getCode();
                    $this->selectInputVars[$this->getFacetPrefix() . $var->getCode()] = $var->getCode();
                    $this->addFacet($var->getName(), $this->getFacetPrefix() . $var->getCode());

                    // skip, if we already have the value
                    if (isset($this->facetFilters[$var->getCode()])) {
                        continue;
                    }

                    $urlVal = $request->get($var->getUrlToken(), '');
                    if (!$urlVal) {
                        continue;
                    }

                    // todo : sanitize
                    $this->addFacetFilter($this->getFacetPrefix() . $var->getCode(), $urlVal);
                }
            }
        }

        $sortableInfo = [];

        if ($sortable) {
            foreach($sortable as $code => $label) {
                $isActive = (int) ($this->getSortBy() == $code);

                $sortableInfo[] = [
                    'code'      => $code,
                    'label'     => $label,
                    'isActive'  => $isActive,
                    'direction' => $this->getSortDir(),
                ];
            }
        }

        $this->sortable = $sortableInfo;
        $this->filterable = $filterable;

        // populate $this->filters[] using keys from entity repository
        if ($filterable) {
            foreach($filterable as $filterInfo) {
                $urlKey = $filterInfo['code'];
                $filterVal = $this->request->get($urlKey, '');

                // avoid conflict in frontend product listing
                if ($urlKey == 'slug'
                    && $this->getCategoryId()
                    && $this->objectType == EntityConstants::PRODUCT) {

                    continue;
                }

                if (strlen($filterVal)) {
                    $this->addFilter($urlKey, $filterVal);
                }
            }
        }

        // build advanced filters: [field operator value]
        $advFilters = [];

        $validOps = $this->getFilterOps();
        if ($filterVals) {
            foreach($filterVals as $idx => $filterVal) {

                $filterVal = trim($filterVal);
                if (!strlen($filterVal)) {
                    continue;
                }

                // todo : have it check advFilterOps
                if (!isset($filterFields[$idx]) || !isset($filterOps[$idx])) {
                    continue;
                }

                $op = $filterOps[$idx];
                if (!isset($validOps[$op])) {
                    continue;
                }

                $field = $filterFields[$idx];

                $aFilter = [
                    'field' => $field,
                    'op'    => $op,
                    'value' => $filterVal,
                    'url_token' => $this->getUrlTokenByFacetCode($field),
                ];

                if ($this->filterable) {
                    foreach($this->filterable as $filterable) {
                        if ($filterable['code'] == $field) {
                            $aFilter['type'] = $filterable['type'];
                            if (isset($filterable['choices'])) {
                                $aFilter['choices'] = $filterable['choices'];
                            }
                            break;
                        }
                    }
                }

                $advFilters[] = $aFilter;
            }
        }

        $this->advFilters = $advFilters;

        return $this;
    }

    /**
     * Load All Item Vars : complete set, no filters
     *
     * @return $this
     */
    public function loadAllVars()
    {
        $this->vars = $this->getEntityService()->findAll(EntityConstants::ITEM_VAR);
        return $this;
    }

    /**
     * @return $this
     */
    public function loadVarsByObjectType()
    {
        $this->vars = $this->getEntityService()
            ->getObjectTypeItemVars($this->getObjectType());

        return $this;
    }

    /**
     * Getter, also a temporary cache
     *
     * @return mixed
     */
    protected function getVars()
    {
        if (!$this->vars) {
            $this->loadVarsByObjectType();
        }

        return $this->vars;
    }

    /**
     * @return array
     */
    protected function getVarDatatypes()
    {
        // todo : flag
        if ($this->varDatatypes) {
            return $this->varDatatypes;
        }

        $this->varDatatypes = [];
        if ($vars = $this->getVars()) {
            $types = [];
            foreach($vars as $var) {
                $datatype = $var->getDatatype();
                $types[$datatype] = $datatype;
            }
            $this->varDatatypes = array_values($types);
        }

        return $this->varDatatypes;
    }

    /**
     * @param $code
     * @return bool
     */
    protected function getVarByCode($code)
    {
        if ($vars = $this->getVars()) {
            foreach($vars as $var) {
                if ($var->getCode() == $code) {
                    return $var;
                }
            }
        }

        return false;
    }

    /**
     * @param $facetCode
     * @param $urlFacetValue
     * @return string
     */
    protected function getRemoveFacetTermUrl($facetCode, $urlFacetValue)
    {
        // get all url parameters,
        // including active facets from the url
        $requestParams = [];

        // todo : make this better , handle &amp;
        foreach(explode('&', $this->getRequest()->getQueryString()) as $keyValueStr) {
            $keyValue = explode('=', $keyValueStr);
            $key = isset($keyValue[0]) ? $keyValue[0] : 0;
            if (!$key) {
                continue;
            }

            $value = isset($keyValue[1])
                ? $keyValue[1]
                : '';

            if ($key == $this->pageParam) {
                $value = 1;
            }

            $requestParams[$key] = explode($this->valueSep, $value);
        }

        $urlToken = $this->getUrlTokenByFacetCode($facetCode);
        $urlValue = $this->getRequest()->get($urlToken, '');
        //$isActive = (strlen($urlValue) > 0);

        // get the base url
        // todo : make this better
        $uriParts = explode('?', $this->getRequest()->getUri());
        $uri = $uriParts[0];

        // explode the url value
        // eg explode(',', 'bla,foo') = array(0 => 'bla', 1 => 'foo')
        $urlValues = explode($this->valueSep, $urlValue);

        $urlParams = [];

        // create remove link if facet is active
        // basically, unset the facet code from the active facet array
        // and rebuild the url

        $newValues = array_flip($urlValues); // eg array('bla' => 0, 'foo' => 1)
        unset($newValues[$urlFacetValue]);
        $newValues = array_flip($newValues);

        foreach($requestParams as $token => $values) {
            // ignore parts of the url that aren't handled
            if ($token == $urlToken) {

                //only include filter if it has multiple values
                if (count($newValues) > 0) {
                    $urlParams[] = $token . '=' . implode($this->valueSep, $newValues);
                }
            } else {
                $urlParams[] = $token . '=' . implode($this->valueSep, $values);
            }
        }

        $removeUrl = $uri;
        if ($urlParams) {
            $removeUrl .= '?' . implode('&', $urlParams);
        }

        // url for removing active facet
        return $removeUrl;
    }

    /**
     * @return $this
     */
    protected function populateFacetLinks()
    {
        // facets and counts are already retrieved
        $facetCounts = $this->getFacetCounts();

        // get all url parameters,
        // including active facets from the url
        $requestParams = [];

        // todo : make this better , handle &amp;
        foreach(explode('&', $this->getRequest()->getQueryString()) as $keyValueStr) {
            $keyValue = explode('=', $keyValueStr);
            $key = isset($keyValue[0]) ? $keyValue[0] : 0;
            if (!$key) {
                continue;
            }

            $value = isset($keyValue[1]) ? $keyValue[1] : '';
            if ($key == $this->pageParam) {
                $value = 1;
            }

            $requestParams[$key] = explode($this->valueSep, $value);
        }

        // get the base url
        // todo : make this better
        $uriParts = explode('?', $this->getRequest()->getUri());
        $uri = $uriParts[0];

        //loop on facets
        // get add link
        // or remove link if the facet is active
        foreach($facetCounts as $facetCode => $data) {
            $urlToken = $this->getUrlTokenByFacetCode($facetCode);
            if (!isset($facetCounts[$facetCode]['urlToken'])) {
                $facetCounts[$facetCode]['urlToken'] = $urlToken;
            }

            $urlValue = $urlToken
                ? $this->getRequest()->get($urlToken, '')
                : '';

            $isActive = (strlen($urlValue) > 0);
            if (!isset($facetCounts[$facetCode]['isActive'])) {
                $facetCounts[$facetCode]['isActive'] = $isActive;
            }

            if (!isset($facetCounts[$facetCode]['label'])) {

                $code = str_replace($this->getFacetPrefix(), '', $facetCode);

                $label = $this->getVarByCode($code)
                    ? $this->getVarByCode($code)->getName()
                    : $code;

                $facetCounts[$facetCode]['label'] = $label;
            }

            $urlValues = explode($this->valueSep, $urlValue);
            if (isset($data['terms']) && $data['terms']) {
                foreach($data['terms'] as $i => $termData) {
                    //$term = $termData['term'];
                    $term = $termData['urlValue'];
                    $urlParams = [];

                    // create remove link if facet is active
                    // basically, unset the facet code from the active facet array
                    // and rebuild the url
                    if (in_array($term, $urlValues)) {

                        $newValues = array_flip($urlValues);
                        unset($newValues[$term]);
                        $newValues = array_flip($newValues);

                        foreach($requestParams as $token => $values) {
                            // ignore parts of the url that aren't handled
                            if ($token == $urlToken) {
                                if (count($newValues) > 0) {
                                    $urlParams[] = $token . '=' . implode($this->valueSep, $newValues);
                                }
                            } else {
                                $urlParams[] = $token . '=' . implode($this->valueSep, $values);
                            }
                        }

                        $removeUrl = $uri;
                        if ($urlParams) {
                            $removeUrl .= '?' . implode('&', $urlParams);
                        }

                        // url for removing active facet
                        $facetCounts[$facetCode]['terms'][$i]['remove_url'] = $removeUrl;
                    } else {

                        if (isset($requestParams[$urlToken])) {
                            // get the values
                            $newValues = $requestParams[$urlToken];

                            // append the current facet code, etc
                            $newValues[] = $term;

                            // build the add facet url
                            foreach($requestParams as $token => $values) {
                                if ($token == $urlToken) {
                                    $urlParams[] = $token . '=' . implode($this->valueSep, $newValues);
                                } else {
                                    $urlParams[] = $token . '=' . implode($this->valueSep, $values);
                                }
                            }
                        } else {
                            $urlParams[] = $urlToken . '=' . $term;
                            if ($requestParams) {
                                foreach($requestParams as $token => $values) {
                                    $urlParams[] = $token . '=' . implode($this->valueSep, $values);
                                }
                            }
                        }

                        // url for adding facet
                        $facetCounts[$facetCode]['terms'][$i]['url'] = $uri . '?' . implode('&', $urlParams);
                    }
                }
            }
        }

        // todo : array_walk or similar
        if ($facetCounts) {
            $newCounts = [];
            foreach($facetCounts as $key => $info) {
                $urlToken = $info['urlToken'];
                $terms = $info['terms'];
                if ($terms) {
                    foreach($terms as $i => $term) {
                        $term['urlToken'] = $urlToken;
                        $terms[$i] = $term;
                    }
                    $info['terms'] = $terms;
                }
                $newCounts[] = $info;
            }
            $facetCounts = $newCounts;
        }
        $this->facetCounts = $facetCounts;
        return $this;
    }

    /**
     * @return mixed
     */
    abstract protected function executeFacetCounts();

    /**
     * @param array $params
     * @return mixed
     */
    abstract public function search(array $params = []);
}
