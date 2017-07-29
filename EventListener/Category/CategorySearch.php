<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CategorySearch
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategorySearch
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
    public function onCategorySearch(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $search = $event->getSearch()
            ->setObjectType($event->getObjectType()) // Important: set this first
            ->parseRequest($event->getRequest())
            //->addJoin('left', EntityConstants::CATEGORY_PRODUCT, 'category_id')
            //->addColumn('count(' . EntityConstants::CATEGORY_PRODUCT . '.product_id)', 'product_count')
            //->addGroupBy('main.id')
        ;

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            $search->setDefaultSort('sort_order', 'asc');
        }

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_category', $request->getQueryString());
        }

        $event->setReturnData($returnData);
    }
}
