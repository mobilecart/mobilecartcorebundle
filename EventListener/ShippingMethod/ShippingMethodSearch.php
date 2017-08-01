<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ShippingMethodSearch
 * @package MobileCart\CoreBundle\EventListener\ShippingMethod
 */
class ShippingMethodSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onShippingMethodSearch(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->search();

        $event->setReturnData($returnData);
    }
}
