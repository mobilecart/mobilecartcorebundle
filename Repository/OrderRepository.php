<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * OrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class OrderRepository
    extends EntityRepository
    implements CartRepositoryInterface
{

    /**
     * @return bool
     */
    public function isEAV()
    {
        return true;
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
            'status' => 'Status',
            'id' => 'ID',
            'total' => 'Total',
            'billing_name' => 'Name',
            'customer_id' => 'Customer ID',
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
                CartRepositoryInterface::CODE  => 'email',
                CartRepositoryInterface::LABEL => 'Email',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'customer_id',
                CartRepositoryInterface::LABEL => 'Customer',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE => 'status',
                CartRepositoryInterface::LABEL => 'Status',
                CartRepositoryInterface::DATATYPE =>  'string',
                'choices' => [
                    [
                        'value' => 'processing',
                        CartRepositoryInterface::LABEL => 'Processing',
                    ],
                    [
                        'value' => 'partially_shipped',
                        CartRepositoryInterface::LABEL => 'Partially Shipped',
                    ],
                    [
                        'value' => 'shipped',
                        CartRepositoryInterface::LABEL => 'Shipped',
                    ],
                    [
                        'value' => 'canceled',
                        CartRepositoryInterface::LABEL => 'Canceled',
                    ],
                ],
            ],
            [
                CartRepositoryInterface::CODE  => 'billing_name',
                CartRepositoryInterface::LABEL => 'Billing Name',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'billing_company',
                CartRepositoryInterface::LABEL => 'Billing Company',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'total',
                CartRepositoryInterface::LABEL => 'Total',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return ['email', 'billing_name', 'billing_company'];
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}
