<?php

namespace MobileCart\CoreBundle\Service;

/**
 * Interface SearchServiceInterface
 * @package MobileCart\CoreBundle\Service
 */
interface SearchServiceInterface
{
    /**
     * @param string $objectType
     * @return mixed
     */
    public function setObjectType($objectType);

    /**
     * @param string $format
     * @return $this
     */
    public function setFormat($format);

    /**
     * @return string
     */
    public function getFormat();

    /**
     * Parse Request , set filters, sort, and paginator parameters
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return $this
     */
    public function parseRequest(\Symfony\Component\HttpFoundation\Request $request);

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addFilter($key, $value);

    /**
     * @param $sortBy
     * @param string $sortDir
     * @return $this
     */
    public function setSort($sortBy, $sortDir = 'asc');

    /**
     * @param $sortBy
     * @param string $sortDir
     * @return $this
     */
    public function setDefaultSort($sortBy, $sortDir = 'asc');

    /**
     * Get sort-by parameter value
     *
     * @return string
     */
    public function getSortBy();

    /**
     * Get sort direction parameter value
     *
     * @return string
     */
    public function getSortDir();

    /**
     * @return string
     */
    public function getDefaultSortBy();

    /**
     * @return string
     */
    public function getDefaultSortDir();

    /**
     * Set page number
     *
     * @param int $page
     * @return $this
     */
    public function setPage($page);

    /**
     * @return int
     */
    public function getPage();

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit);

    /**
     * @return int
     */
    public function getLimit();

    /**
     * @return $this
     */
    public function search();

    /**
     * @return array|mixed
     */
    public function getResult();
}
