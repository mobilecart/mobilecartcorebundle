<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerAddressSearch
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onCustomerAddressSearch(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            ->addFilter('customer_id', $event->getUser()->getId());

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        $event->setReturnData($returnData);
    }
}
