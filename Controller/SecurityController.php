<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    // no annotations here
    public function loginAction(Request $request)
    {
        $helper = $this->get('security.authentication_utils');

        $returnData = [
            'last_username' => $helper->getLastUsername(),
            'error'         => $helper->getLastAuthenticationError(),
        ];

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::LOGIN_VIEW_RETURN, $event);

        return $this->get('cart.theme')
            ->render('frontend', 'Security:login.html.twig', $event->getReturnData());
    }

    // no annotations here
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }
}
