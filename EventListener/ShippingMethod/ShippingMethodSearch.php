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
    public function onShippingMethodSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $this->getSearch()
            ->parseRequest($request);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_shipping_method', $request->getQueryString());
        }
    }
}
