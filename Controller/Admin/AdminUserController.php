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
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class AdminUserController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class AdminUserController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::ADMIN_USER;

    /**
     * Lists Admin User entities
     */
    public function indexAction(Request $request)
    {
        // in_array('ROLE_ADMIN', $this->getUser()->getRoles())

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ITEM_SEARCH, $event);

        return $event->getResponse();
    }

    // todo editAction()

    // todo updateAction()

}
