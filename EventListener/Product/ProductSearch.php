<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ProductSearch
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductSearch
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
    public function onProductSearch(CoreEvent $event)
    {
        $request = $event->getRequest();

        // custom logic . tweak as needed
        $loadVarValues = false;

        $categoryId = $event->getCategory()
            ? $event->getCategory()->getId()
            : 0;

        $filters = [];
        switch($event->getSection()) {
            case CoreEvent::SECTION_FRONTEND:

                $filters = [
                    'is_in_stock' => 1,
                    'is_public' => 1,
                    'is_enabled' => 1,
                ];

                break;
            case CoreEvent::SECTION_BACKEND:
                //$loadVarValues = true;
                break;
            case CoreEvent::SECTION_API:
                $loadVarValues = true;
                break;
            default:

                break;
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {
            $loadVarValues = 1;
        }

        $search = $this->getSearch()
            ->setCategoryId($categoryId)
            ->setPopulateVarValues($loadVarValues)
            ->parseRequest($request)
            ->addFilters($filters);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            $search->setDefaultSort('sort_order', 'asc');
        }

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search());

        if ($event->getCategory()) {
            $event->setReturnData('category', $event->getCategory());
        }

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_product', $request->getQueryString());
        }
    }
}
