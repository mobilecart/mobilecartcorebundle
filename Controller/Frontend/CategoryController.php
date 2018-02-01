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
use Symfony\Component\HttpFoundation\Request;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class CategoryController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class CategoryController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CATEGORY;

    /**
     * List Category entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_SEARCH, $event);

        return $event->getResponse();
    }

    // viewAction is in ProductController
}
