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

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class OrderItemController extends Controller
{
    protected $objectType = EntityConstants::ORDER_ITEM;

    /**
     * Lists Order Item entities.
     *
     * @Route("/", name="cart_admin_order_item")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        // Load a service; which extends Search\SearchAbstract
        // The service parameter is stored in the service configuration as a parameter ; (slightly meta)
        // This service could use either MySQL or ElasticSearch, etc for retrieving item data
        $searchParam = $this->container->getParameter('cart.load.admin');
        $search = $this->container->get($searchParam);

        // Observe Event :
        //  perform custom logic, post-processing

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setSearch($search)
            ->setObjectType(EntityConstants::ORDER_ITEM)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ITEM_SEARCH, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ITEM_LIST, $event);

        return $event->getResponse();
    }
}
