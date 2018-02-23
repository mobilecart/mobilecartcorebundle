<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class OrderShipmentRepository
 * @package MobileCart\CoreBundle\Repository
 */
class OrderShipmentRepository
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
        return [
            'id' => 'ID',
            'name' => 'Name',
            'company_name' => 'Customer Company',
            'company' => 'Shipping Company',
            'method' => 'Shipping Method',
            'created_at' => 'Created At'
        ];
    }

    /**
     * @return array
     */
    public function getFilterableFields()
    {
        return [
            [
                CartRepositoryInterface::CODE  => 'id',
                CartRepositoryInterface::LABEL => 'ID',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'name',
                CartRepositoryInterface::LABEL => 'Name',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'street',
                CartRepositoryInterface::LABEL => 'Street',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'city',
                CartRepositoryInterface::LABEL => 'City',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'postcode',
                CartRepositoryInterface::LABEL => 'Postal Code',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'tracking',
                CartRepositoryInterface::LABEL => 'Tracking',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return ['name', 'street', 'city', 'postcode', 'tracking'];
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
