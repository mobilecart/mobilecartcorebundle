<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Content controller.
 *
 * @Route("/admin")
 */
class DashboardController extends Controller
{

    /**
     * Display dashboard
     *
     * @Route("/", name="cart_admin_dashboard")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::DASHBOARD_VIEW_RETURN, $event);

        return $event->getResponse();
    }

}
