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
 * Class ProductController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class ProductController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::PRODUCT;

    /**
     * View product detail page
     */
    public function viewAction(Request $request)
    {
        $entity = $this->get('cart.entity')->findOneBy(EntityConstants::PRODUCT, [
            'slug' => $request->get('slug', ''),
        ]);

        $isAdmin = ($this->getUser() && in_array('ROLE_ADMIN', $this->getUser()->getRoles()));
        if (!$entity || (!$entity->getIsPublic() && !$isAdmin)) {
            throw $this->createNotFoundException('Unable to find Product');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setEntity($entity)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADDTOCART_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Search and list products
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Search and list products within a category
     */
    public function categoryAction(Request $request)
    {
        $category = $this->get('cart.entity')->findOneBy(EntityConstants::CATEGORY, [
            'slug' => $request->get('slug', ''),
        ]);

        if (!$category) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND)
            ->setCategory($category);

        // todo : look into current display modes, make sure it's all enabled

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $event);

        return $event->getResponse();
    }
}
