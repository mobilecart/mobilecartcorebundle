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

class ProductController extends Controller
{
    protected $objectType = EntityConstants::PRODUCT;

    public function viewAction(Request $request)
    {
        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $entity = $entityService->findOneBy(EntityConstants::PRODUCT, [
            'slug' => $request->get('slug', ''),
        ]);

        $isAdmin = ($this->getUser() && in_array('ROLE_ADMIN', $this->getUser()->getRoles()));

        if (!$entity || (!$entity->getIsPublic() && !$isAdmin)) {
            throw $this->createNotFoundException('Unable to find Product');
        }

        $formEvent = new CoreEvent();
        $formEvent->setEntity($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADDTOCART_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData())
            ->setEntity($entity)
            ->setForm($formEvent->getForm())
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_VIEW_RETURN, $event);

        return $event->getResponse();
    }

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
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $searchEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($searchEvent->getReturnData())
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_LIST, $event);

        return $event->getResponse();
    }

    public function categoryAction(Request $request)
    {
        $searchParam = $this->container->getParameter('cart.search.frontend');
        $search = $this->container->get($searchParam);

        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $category = $entityService->findOneBy(EntityConstants::CATEGORY, [
            'slug' => $request->get('slug', ''),
        ]);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $searchEvent = new CoreEvent();
        $searchEvent->setRequest($request)
            ->setSearch($search)
            ->setCategory($category)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        if ($category->getDisplayMode() != EntityConstants::DISPLAY_TEMPLATE) {

            // don't need to search for products if we're not displaying any

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::PRODUCT_SEARCH, $searchEvent);
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($searchEvent->getReturnData())
            ->setCategory($category)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_LIST, $event);

        return $event->getResponse();
    }
}
