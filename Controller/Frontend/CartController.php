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
            ->set('user', $this->getUser())
            ->set('is_multi_shipping_enabled', $this->getParameter('cart.shipping.multi.enabled'));

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Add product to shopping cart
     */
    public function addProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->set('is_add', true)
            ->set('user', $this->getUser())
            ->set('is_multi_shipping_enabled', $this->getParameter('cart.shipping.multi.enabled'));

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_PRODUCT, $event);

        return $event->getResponse();
    }

    /**
     * Add shipment to shopping cart
     */
    public function addShipmentAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->set('user', $this->getUser())
            ->set('is_multi_shipping_enabled', $this->getParameter('cart.shipping.multi.enabled'));

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_SHIPMENT, $event);

        return $event->getResponse();
    }

    /**
     * Update product qty's in shopping cart
     */
    public function updateQtysAction(Request $request)
    {
        $qtys = $request->get('qty', []);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $success = true;

        if (is_array($qtys) && $qtys) {
            foreach($qtys as $productId => $qty) {
                if ($qty < 1) {

                    $event = new CoreEvent();
                    $event->setRequest($request)
                        ->setIsMassUpdate(true)
                        ->setUser($this->getUser())
                        ->set('product_id', $productId)
                        ->set('is_multi_shipping_enabled', $this->getParameter('cart.shipping.multi.enabled'));

                    $this->get('event_dispatcher')
                        ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $event);

                    if (!$event->getReturnData('success')) {
                        $success = false;
                    }

                } else {

                    $event = new CoreEvent();
                    $event->setRequest($request)
                        ->setIsMassUpdate(true)
                        ->setUser($this->getUser())
                        ->set('product_id', $productId)
                        ->set('qty', $qty)
                        ->set('is_add', false)
                        ->set('is_multi_shipping_enabled', $this->getParameter('cart.shipping.multi.enabled'));

                    $this->get('event_dispatcher')
                        ->dispatch(CoreEvents::CART_ADD_PRODUCT, $event);

                    if (!$event->getReturnData('success')) {
                        $success = false;
                    }
                }
            }
        }

        if ($success) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Cart Updated'
            );
        }

        return $event->getResponse();
    }

    /**
     * Remove product from shopping cart
     */
    public function removeProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->set('user', $this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $event);

        return $event->getResponse();
    }

    /**
     * Remove all products from shopping cart
     */
    public function removeProductsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->set('user', $this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCTS, $event);

        return $event->getResponse();
    }

    /**
     * Retrieve totals for shopping cart
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

    /**
     * Submit discount code to shopping cart
     */
    public function addDiscountAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->set('user', $this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_DISCOUNT, $event);

        return $event->getResponse();
    }
}
