<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ProductSearch
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductSearch
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function onProductSearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();

        // custom logic . tweak as needed
        $loadVarValues = 0;

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
                //$loadVarValues = 1;
                break;
            case CoreEvent::SECTION_API:
                $loadVarValues = 1;
                break;
            default:

                break;
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {
            $loadVarValues = 1;
        }

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->setCategoryId($categoryId)
            ->setPopulateVarValues($loadVarValues);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            $search->setDefaultSort('sort_order', 'asc');
        }

        $search->parseRequest($event->getRequest())
            ->addFilters($filters);

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        if ($event->getCategory()) {
            $returnData['category'] = $event->getCategory();
        }

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_product', $request->getQueryString());
        }

        $event->setReturnData($returnData);
    }
}
