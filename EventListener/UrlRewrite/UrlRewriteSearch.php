<?php

namespace MobileCart\CoreBundle\EventListener\UrlRewrite;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class UrlRewriteSearch
 * @package MobileCart\CoreBundle\EventListener\UrlRewrite
 */
class UrlRewriteSearch
{
    /**
     * @param CoreEvent $event
     */
    public function onUrlRewriteSearch(CoreEvent $event)
    {
        $request = $event->getRequest();
        $search = $event->getSearch()
            ->parseRequest($request);

        $event->setReturnData('search', $search);
        $event->setReturnData('result', $search->search()->getResult());

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_url_rewrite', $request->getQueryString());
        }
    }
}
