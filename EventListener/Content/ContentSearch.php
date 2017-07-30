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
     * @param CoreEvent $event
     */
    public function onContentSearch(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
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
            case CoreEvent::SECTION_API:
                // no-op
                break;
            default:

                break;
        }

        $search = $event->getSearch()
            ->parseRequest($event->getRequest())
            ->addFilters($filters);

        $returnData['search'] = $search;
        $returnData['result'] = $search->search();
        $search->getEntityService()->populateData(EntityConstants::CONTENT_SLOT, $returnData['result']['entities']);

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_content', $request->getQueryString());
        }

        $event->setReturnData($returnData);
    }
}
