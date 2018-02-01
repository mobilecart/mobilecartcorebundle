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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcher;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CartController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class CartController extends Controller
{
    /**
     * Display shopping cart
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser())
            ->set('is_multi_shipping_enabled', $this->getParameter('cart.shipping.multi.enabled'));

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * API call : initialize shopping cart and return its hash ID
     */
    public function initAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_INIT, $event);

        return $event->getResponse();
    }

    /**
     * Add product to shopping cart
     */
    public function addProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser())
            ->set('is_add', true);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_PRODUCT, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_TOTALS_SHIPPING, $event);

        return $event->getResponse();
    }

    /**
     * Add shipment to shopping cart
     */
    public function addShipmentAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_SHIPMENT, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_TOTALS_SHIPPING, $event);

        return $event->getResponse();
    }

    /**
     * Update product qty's in shopping cart
     */
    public function updateQtysAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_ITEM_QTYS, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_TOTALS_SHIPPING, $event);

        return $event->getResponse();
    }

    /**
     * Remove product from shopping cart
     */
    public function removeProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_TOTALS_SHIPPING, $event);

        return $event->getResponse();
    }

    /**
     * Remove all products from shopping cart
     */
    public function removeProductsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCTS, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_TOTALS_SHIPPING, $event);

        return $event->getResponse();
    }

    /**
     * Retrieve totals for shopping cart
     */
    public function totalsAction(Request $request)
    {
        // todo : move this to event listener . add logic for handling api requests, etc
        $totalsMap = $this->get('cart')
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

    /**
     * For api customers who login after creating a cart
     */
    public function claimCartAction(Request $request)
    {
        // create event

        // create listener
        // * require CustomerEntity on CartService
        // * save customer_id on cart

        //
    }

    /**
     * Submit discount code to shopping cart
     */
    public function addDiscountAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_DISCOUNT, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_TOTALS_SHIPPING, $event);

        return $event->getResponse();
    }

    /**
     * Remove discount code from shopping cart
     */
    public function removeDiscountAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_DISCOUNT, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_UPDATE_TOTALS_SHIPPING, $event);

        return $event->getResponse();
    }
}
