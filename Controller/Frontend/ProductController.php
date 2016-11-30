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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

class ProductController extends Controller
{
    protected $objectType = EntityConstants::PRODUCT;

    /**
     * @Route("/product/{slug}", name="cart_product_view")
     * @Method("GET")
     */
    public function viewAction(Request $request)
    {
        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $entity = $entityService->findOneBy(EntityConstants::PRODUCT, [
            'slug' => $request->get('slug', ''),
        ]);

        $isAdmin = ($this->getUser() && in_array('ROLE_ADMIN', $this->getUser()->getRoles()));

        if (!$entity || (!$entity->getIsPublic() && !$isAdmin)) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $addToCartForm = $this->createAddToCartForm($entity->getId());

        $configData = @ (array) json_decode($entity->getConfig());

        $returnData = [
            'entity'         => $entity,
            'addtocart_form' => $addToCartForm->createView(),
            'config_data'    => $configData,
        ];

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData)
            ->setEntity($entity)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/products", name="cart_products")
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

    /**
     * @Route("/category/{slug}", name="cart_category_products")
     * @Method("GET")
     */
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

        // todo : add data to return data for customizing facet links

        $searchEvent = new CoreEvent();
        $searchEvent->setRequest($request)
            ->setSearch($search)
            ->setCategory($category)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $searchEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($searchEvent->getReturnData())
            ->setSection(CoreEvent::SECTION_FRONTEND);

        // todo : add data to return data for customizing form action of listing form

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_LIST, $event);

        return $event->getResponse();
    }

    private function createAddToCartForm($id)
    {
        return $this->createFormBuilder(['id' => $id, 'qty' => 1])
            ->add('id', 'hidden')
            ->add('qty', 'text')
            ->getForm()
        ;
    }
}
