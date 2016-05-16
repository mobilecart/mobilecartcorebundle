<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Repository;

/**
 * Interface CartRepositoryInterface
 *
 * Methods for allowing the framework to get
 *  additional information about entities
 *  eg which fields are sortable, filterable
 *  or if the Entity is EAV
 */
interface CartRepositoryInterface
{
    /**
     * Search Method : LIKE
     *  mostly for RDBMS, use a SQL LIKE operator
     */
    const SEARCH_METHOD_LIKE = 1;

    /**
     * Search Method : Fulltext
     *  use a fulltext search method
     */
    const SEARCH_METHOD_FULLTEXT = 2;

    /**
     * Array of entity fields which are sortable
     *
     * @return array
     */
    public function getSortableFields();

    /**
     * Array of entity fields which are filterable
     *
     * @return array
     */
    public function getFilterableFields();

    /**
     * Flag for detecting EAV-based entity
     *
     * @return bool
     */
    public function isEAV();

    /**
     * @return string|array
     */
    public function getSearchField();

    /**
     * @return mixed
     */
    public function getSearchMethod();
}
