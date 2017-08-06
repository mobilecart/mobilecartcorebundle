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
    public function onCustomerAddressSearch(CoreEvent $event)
    {
        $search = $this->getSearch()
            ->parseRequest($event->getRequest());

        switch($event->getSection()) {
            case CoreEvent::SECTION_FRONTEND:
                $search->addFilter('customer_id', $event->getUser()->getId());
                break;
            case CoreEvent::SECTION_BACKEND:

                break;
            default:

                break;
        }

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());
    }
}
