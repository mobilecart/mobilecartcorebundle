<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\Constants\EntityConstants;

class GeographyService
{

    protected $entityService;

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $countryCode
     * @return array
     */
    public function getRegionChoicesByCountry($countryCode)
    {
        $choices = [];

        $regions = $this->getEntityService()->findBy(EntityConstants::REF_COUNTRY_REGION,[
            'country_code' => $countryCode,
        ],[
            'region_code' => 'asc',
        ]);

        if ($regions) {
            foreach($regions as $region) {
                $choices[$region->getRegionCode()] = $region->getRegionName();
            }
        }

        return $choices;
    }

    /**
     * @param array $countryCodes
     * @return array $countries with format ['US' => ['AK' => 'Arkansas'],['AL' => 'Alabama]]]
     */
    public function getRegionsByCountries(array $countryCodes)
    {
        $countries = [];

        $regions = $this->getEntityService()->findBy(EntityConstants::REF_COUNTRY_REGION,[
            'country_code' => $countryCodes,
        ],[
            'country_code' => 'asc',
            'region_code' => 'asc',
        ]);

        if ($regions) {
            foreach($regions as $region) {

                if (!isset($countries[$region->getCountryCode()])) {
                    $countries[$region->getCountryCode()] = [];
                }

                $countries[$region->getCountryCode()][$region->getRegionCode()] = $region->getRegionName();
            }
        }

        return $countries;
    }
}
