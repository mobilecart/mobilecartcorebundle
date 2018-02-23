<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class OrderPaymentRepository
 * @package MobileCart\CoreBundle\Repository
 */
class OrderPaymentRepository
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
            'base_amount' => 'Amount',
            CartRepositoryInterface::CODE => 'Service Code',
            'created_at' => 'Created At',
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
                CartRepositoryInterface::CODE  => 'base_amount',
                CartRepositoryInterface::LABEL => 'Amount',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => CartRepositoryInterface::CODE,
                CartRepositoryInterface::LABEL => 'Service Code',
                CartRepositoryInterface::DATATYPE  => 'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'created_at',
                CartRepositoryInterface::LABEL => 'Created At',
                CartRepositoryInterface::DATATYPE  => 'date',
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
