<?php

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

class ExportController extends Controller
{
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::EXPORT_OPTIONS_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    public function runAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::EXPORT_RUN, $event);

        return $event->getResponse();
    }
}
