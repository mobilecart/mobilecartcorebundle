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
    /**
     * @var string
     */
    protected $objectType = EntityConstants::PRODUCT;

    public function viewAction(Request $request)
    {
        // slightly meta - get service id from config parameter and load entity service
        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam)
            ->setObjectType($this->objectType);

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
        // slightly meta - get a service id from a config parameter and load the service
        //  doing it this way because replacing a parameter in your own bundle is very easy
        $searchParam = $this->container->getParameter('cart.search.frontend');
        $search = $this->container->get($searchParam)
            ->setObjectType($this->objectType);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setSearch($search)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $event);

        return $event->getResponse();
    }

    public function categoryAction(Request $request)
    {
        // slightly meta - get a service id from a config parameter and load the service
        //  doing it this way because replacing a parameter in your own bundle is very easy
        $searchParam = $this->container->getParameter('cart.search.frontend');
        $search = $this->container->get($searchParam)
            ->setObjectType($this->objectType);

        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $category = $entityService->findOneBy(EntityConstants::CATEGORY, [
            'slug' => $request->get('slug', ''),
        ]);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setSearch($search)
            ->setCategory($category)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        // todo : look into current display modes, make sure it's all enabled

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $event);

        return $event->getResponse();
    }
}
