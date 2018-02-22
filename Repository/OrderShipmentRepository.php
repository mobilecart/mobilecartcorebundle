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
                'code'  => 'id',
                'label' => 'ID',
                'datatype' =>  'number',
            ],
            [
                'code'  => 'name',
                'label' => 'Name',
                'datatype' =>  'string',
            ],
            [
                'code'  => 'street',
                'label' => 'Street',
                'datatype' =>  'string',
            ],
            [
                'code'  => 'city',
                'label' => 'City',
                'datatype' =>  'string',
            ],
            [
                'code'  => 'postcode',
                'label' => 'Postal Code',
                'datatype' =>  'string',
            ],
            [
                'code'  => 'tracking',
                'label' => 'Tracking',
                'datatype' =>  'string',
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
