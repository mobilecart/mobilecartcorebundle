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
            'code' => 'Service Code',
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
                'code'  => 'id',
                'label' => 'ID',
                'type'  => 'number',
            ],
            [
                'code'  => 'base_amount',
                'label' => 'Amount',
                'type'  => 'number',
            ],
            [
                'code'  => 'code',
                'label' => 'Service Code',
                'type'  => 'string',
            ],
            [
                'code'  => 'created_at',
                'label' => 'Created At',
                'type'  => 'date',
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
