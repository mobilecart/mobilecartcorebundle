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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\EventDispatcher\EventDispatcher;

use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

class CartController extends Controller
{
    /**
     * @Route("/cart/view", name="cart_view")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add_item")
     * @Method("POST")
     */
    public function addProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setIsAdd(1);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_PRODUCT, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/shipment/add", name="cart_shipment_add")
     * @Method("POST")
     */
    public function addShipmentAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_SHIPMENT, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/update-qtys", name="cart_update_qtys")
     * @Method("POST")
     */
    public function updateQtysAction(Request $request)
    {
        $qtys = $request->get('qty', []);

        $event = new CoreEvent();
        if (is_array($qtys) && $qtys) {

            foreach($qtys as $productId => $qty) {
                if ($qty < 1) {

                    $event = new CoreEvent();
                    $event->setRequest($request);

                    $this->get('event_dispatcher')
                        ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $event);

                } else {

                    $event = new CoreEvent();
                    $event->setRequest($request)
                        ->setProductId($productId)
                        ->setQty($qty)
                        ->setIsAdd(0);

                    $this->get('event_dispatcher')
                        ->dispatch(CoreEvents::CART_ADD_PRODUCT, $event);

                }
            }
        }

        return $event->getResponse();
    }
    
    /**
     * @Route("/cart/remove/{product_id}", name="cart_remove_item")
     * @Method("GET")
     */
    public function removeProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/products/remove", name="cart_remove_items")
     * @Method("GET")
     */
    public function removeProductsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCTS, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/totals", name="cart_totals")
     * @Method("GET")
     */
    public function totalsAction(Request $request)
    {
        $totalsMap = $this->get('cart.session')
            ->collectTotals()
            ->getTotals();

        $totals = [];
        if ($totalsMap) {
            foreach($totalsMap as $total) {

                $totals[] = [
                    'label' => $total->getLabel(),
                    'value' => $total->getValue(),
                ];
            }
        }

        return new JsonResponse($totals);
    }
}
