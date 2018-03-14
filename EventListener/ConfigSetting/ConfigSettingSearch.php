<?php

namespace MobileCart\CoreBundle\EventListener\ConfigSetting;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ConfigSettingSearch
 * @package MobileCart\CoreBundle\EventListener\ConfigSetting
 */
class ConfigSettingSearch
{
    /**
     * @var \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    protected $search;

    /**
     * @param \MobileCart\CoreBundle\Service\SearchServiceInterface $search
     * @param $objectType
     * @return $this
     */
    public function setSearch(\MobileCart\CoreBundle\Service\SearchServiceInterface $search, $objectType)
    {
        $this->search = $search->setObjectType($objectType);
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param CoreEvent $event
     */
    public function onConfigSettingSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $this->getSearch()
            ->parseRequest($request);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search()->getResult());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_config_setting', $request->getQueryString());
        }
    }
}
