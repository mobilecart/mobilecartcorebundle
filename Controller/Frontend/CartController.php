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

class CartController extends Controller
{
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser())
            ->setIsMultiShippingEnabled($this->getParameter('cart.shipping.multi.enabled'))
        ;

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    public function addProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setIsAdd(1)
            ->setUser($this->getUser())
            ->setIsMultiShippingEnabled($this->getParameter('cart.shipping.multi.enabled'))
        ;

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_PRODUCT, $event);

        return $event->getResponse();
    }

    public function addShipmentAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser())
            ->setIsMultiShippingEnabled($this->getParameter('cart.shipping.multi.enabled'))
        ;

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_SHIPMENT, $event);

        return $event->getResponse();
    }

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
                        ->setUser($this->getUser())
                        ->setProductId($productId)
                        ->setIsMassUpdate(1)
                        ->setIsMultiShippingEnabled($this->getParameter('cart.shipping.multi.enabled'))
                    ;

                    $this->get('event_dispatcher')
                        ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $event);

                    if (!$event->getSuccess()) {
                        $success = false;
                    }

                } else {

                    $event = new CoreEvent();
                    $event->setRequest($request)
                        ->setProductId($productId)
                        ->setQty($qty)
                        ->setIsAdd(0)
                        ->setUser($this->getUser())
                        ->setIsMassUpdate(1)
                        ->setIsMultiShippingEnabled($this->getParameter('cart.shipping.multi.enabled'))
                    ;

                    $this->get('event_dispatcher')
                        ->dispatch(CoreEvents::CART_ADD_PRODUCT, $event);

                    if (!$event->getSuccess()) {
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

    public function removeProductAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $event);

        return $event->getResponse();
    }

    public function removeProductsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_REMOVE_PRODUCTS, $event);

        return $event->getResponse();
    }

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

    public function addDiscountAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CART_ADD_DISCOUNT, $event);

        return $event->getResponse();
    }
}
