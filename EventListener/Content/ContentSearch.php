<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ContentSearch
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentSearch
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
    public function onContentSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $filters = [];
        switch($event->getSection()) {
            case CoreEvent::SECTION_FRONTEND:
                $filters = [
                    'is_public' => 1,
                ];
                break;
            case CoreEvent::SECTION_BACKEND:
                // no-op
                break;
            default:

                break;
        }

        $search = $this->getSearch()
            ->parseRequest($event->getRequest())
            ->addFilters($filters);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            $search->setDefaultSort('sort_order', 'asc');
        }

        $event->setReturnData('search', $search);
        $result = $search->search()->getResult();
        $event->setReturnData('result', $result);
        $search->getEntityService()->populateData(EntityConstants::CONTENT_SLOT, $result['entities']);

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_content', $request->getQueryString());
        }
    }
}
