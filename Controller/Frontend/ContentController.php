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
 * Class ContentController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class ContentController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CONTENT;

    /**
     * View a Content page
     */
    public function viewAction(Request $request)
    {
        $isAdmin = ($this->getUser() && in_array('ROLE_ADMIN', $this->getUser()->getRoles()));

        $entity = $this->get('cart.entity')->findOneBy(EntityConstants::CONTENT, [
            'slug' => $request->get('slug', ''),
        ]);

        if (!$entity || (!$entity->getIsPublic() && !$isAdmin)) {
            throw $this->createNotFoundException("Unable to find Content entity");
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setEntity($entity)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * List Content pages
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SEARCH, $event);

        return $event->getResponse();
    }
}