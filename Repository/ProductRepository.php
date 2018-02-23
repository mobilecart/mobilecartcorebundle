<?php

namespace MobileCart\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ProductRepository
 * @package MobileCart\CoreBundle\Repository
 */
class ProductRepository
    extends EntityRepository
    implements CartRepositoryInterface, AdvSortableInterface
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
        return true;
    }

    /**
     * @return array
     */
    public function getSortableFields()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'page_title' => 'Page Title',
            'name' => 'Name',
            'slug' => 'Slug',
            'sort_order' => 'Sort Order',
            'type' => 'Product Type',
            'sku' => 'SKU',
            'price' => 'Price',
            'special_price' => 'Special Price',
            'qty' => 'Qty',
            'is_in_stock' => 'In Stock',
            'is_taxable' => 'Taxable',
        ];
    }

    /**
     * @return array
     */
    public function getAdvSortableFields()
    {
        return [
            'sort_order' => [
                CartRepositoryInterface::LABEL => 'Sort Order',
                'active' => 0,
                'value' => 'sort_order',
                'dir' => 'asc',
            ],
            'price_asc' => [
                CartRepositoryInterface::LABEL => 'Lowest Price',
                'active' => 0,
                'value' => 'price',
                'dir' => 'asc',
            ],
            'price_desc' => [
                CartRepositoryInterface::LABEL => 'Highest Price',
                'active' => 0,
                'value' => 'price',
                'dir' => 'desc',
            ],
            'created_at_newest' => [
                CartRepositoryInterface::LABEL => 'Newest',
                'active' => 0,
                'value' => 'created_at',
                'dir' => 'desc',
            ],
            'name_az' => [
                CartRepositoryInterface::LABEL => 'Name (A-Z)',
                'active' => 0,
                'value' => 'name',
                'dir' => 'asc',
            ],
            'name_za' => [
                CartRepositoryInterface::LABEL => 'Name (Z-A)',
                'active' => 0,
                'value' => 'name',
                'dir' => 'desc',
            ],
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
                CartRepositoryInterface::CODE  => 'category_id',
                CartRepositoryInterface::LABEL => 'Category',
                CartRepositoryInterface::DATATYPE =>  'number',
                'join' => [
                    'type' => 'left', // left, inner, etc
                    'table' => EntityConstants::CATEGORY_PRODUCT,
                    'column' => 'product_id', // eg product_id , without the prefix
                    'join_alias' => 'main', // main table alias
                    'join_column' => 'id', // eg id, item_var_set_id, etc
                ]
            ],
            [
                CartRepositoryInterface::CODE  => 'created_at',
                CartRepositoryInterface::LABEL => 'Created At',
                CartRepositoryInterface::DATATYPE =>  'date',
            ],
            [
                CartRepositoryInterface::CODE  => 'sort_order',
                CartRepositoryInterface::LABEL => 'Sort Order',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'page_title',
                CartRepositoryInterface::LABEL => 'Page Title',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'slug',
                CartRepositoryInterface::LABEL => 'Slug',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'type',
                CartRepositoryInterface::LABEL => 'Product Type',
                CartRepositoryInterface::DATATYPE =>  'number',
                'choices' => [
                    [
                        'value' => 1,
                        CartRepositoryInterface::LABEL => 'Simple'
                    ],
                    [
                        'value' => 2,
                        CartRepositoryInterface::LABEL => 'Configurable',
                    ],
                ],
            ],
            [
                CartRepositoryInterface::CODE  => 'sku',
                CartRepositoryInterface::LABEL => 'SKU',
                CartRepositoryInterface::DATATYPE =>  'string',
            ],
            [
                CartRepositoryInterface::CODE  => 'price',
                CartRepositoryInterface::LABEL => 'Price',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'special_price',
                CartRepositoryInterface::LABEL => 'Special Price',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'qty',
                CartRepositoryInterface::LABEL => 'Qty',
                CartRepositoryInterface::DATATYPE =>  'number',
            ],
            [
                CartRepositoryInterface::CODE  => 'is_in_stock',
                CartRepositoryInterface::LABEL => 'In Stock',
                CartRepositoryInterface::DATATYPE =>  'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        CartRepositoryInterface::LABEL => 'No',
                    ],
                    [
                        'value' => 1,
                        CartRepositoryInterface::LABEL => 'Yes',
                    ],
                ],
            ],
            [
                CartRepositoryInterface::CODE  => 'is_enabled',
                CartRepositoryInterface::LABEL => 'Enabled',
                CartRepositoryInterface::DATATYPE =>  'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        CartRepositoryInterface::LABEL => 'No',
                    ],
                    [
                        'value' => 1,
                        CartRepositoryInterface::LABEL => 'Yes',
                    ],
                ],
            ],
            [
                CartRepositoryInterface::CODE  => 'is_public',
                CartRepositoryInterface::LABEL => 'Public',
                CartRepositoryInterface::DATATYPE =>  'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        CartRepositoryInterface::LABEL => 'No',
                    ],
                    [
                        'value' => 1,
                        CartRepositoryInterface::LABEL => 'Yes',
                    ],
                ],
            ],
            [
                CartRepositoryInterface::CODE  => 'is_taxable',
                CartRepositoryInterface::LABEL => 'Taxable',
                CartRepositoryInterface::DATATYPE =>  'boolean',
                'choices' => [
                    [
                        'value' => 0,
                        CartRepositoryInterface::LABEL => 'No',
                    ],
                    [
                        'value' => 1,
                        CartRepositoryInterface::LABEL => 'Yes',
                    ],
                ],
            ],
        ];

    }

    /**
     * @return array|string
     */
    public function getSearchField()
    {
        return ['name', 'sku', 'fulltext_search'];
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_FULLTEXT;
    }
}
