<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class CommonRepository
 * @package MobileCart\CoreBundle\Repository
 */
class CommonRepository
    extends EntityRepository
    implements CartRepositoryInterface
{
    /**
     * @return bool
     */
    public function isEAV()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasImages()
    {
        return false;
    }

    /**
     * @return array
     */
    public function getSortableFields()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return [];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return '';
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
