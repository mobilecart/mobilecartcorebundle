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

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

class WebhookController extends Controller
{
    public function indexAction(Request $request)
    {
        $input = @file_get_contents("php://input");
        $entity = $this->get('cart.entity')->getInstance(EntityConstants::WEBHOOK_LOG);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setInput($input)
            ->setEntity($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::WEBHOOK_LOG_INSERT, $event);

        return $event->getResponse();
    }
}
