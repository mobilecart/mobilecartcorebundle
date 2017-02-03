<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

class HomeController extends Controller
{
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::HOME_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    public function testAction(Request $request)
    {
        $order = $this->get('cart.entity')->find('order', 1);

        $returnData = [
            'shop_url' => 'shop.com',
            'shop_name' => 'Test Shop',
            'shop_email' => 'test@shop.com',
            'shop_logo' => '/bundles/mobilecartcore/uploads/cart_item/yoda.jpeg',
            'entity' => $order,
        ];

        return $this->get('cart.theme')->render('frontend', 'Order:confirmation.html.twig', $returnData);
    }
}
