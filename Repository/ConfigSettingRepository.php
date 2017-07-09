<?php

namespace MobileCart\CoreBundle\Repository;

/**
 * ConfigSettingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ConfigSettingRepository
    extends \Doctrine\ORM\EntityRepository
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
            'code' => 'Code',
            'label' => 'Label',
            'value' => 'Value'
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
                'code' => 'code',
                'label' => 'Code',
                'type' => 'string',
            ],
            [
                'code' => 'label',
                'label' => 'Label',
                'type' => 'string',
            ],
            [
                'code' => 'value',
                'label' => 'Value',
                'type' => 'string',
            ]
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSearchField()
    {
        return ['code', 'label', 'value'];
    }

    /**
     * @return int|mixed
     */
    public function getSearchMethod()
    {
        return self::SEARCH_METHOD_LIKE;
    }
}