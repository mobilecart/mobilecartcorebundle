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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

class ContentController extends Controller
{
    protected $objectType = EntityConstants::CONTENT;

    /**
     * @Route("/content/{slug}", name="cart_content_view")
     */
    public function viewAction(Request $request)
    {
        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $entity = $entityService->findOneBy(EntityConstants::CONTENT, [
            'slug' => $request->get('slug', ''),
        ]);

        if (!$entity) {
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
     * @Route("/contents", name="cart_contents")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $searchParam = $this->container->getParameter('cart.search.frontend');
        $search = $this->container->get($searchParam);

        $searchEvent = new CoreEvent();
        $searchEvent->setRequest($request)
            ->setSearch($search)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SEARCH, $searchEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($searchEvent->getReturnData())
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_LIST, $event);

        return $event->getResponse();
    }
}