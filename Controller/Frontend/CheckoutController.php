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
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class CheckoutController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class CheckoutController extends Controller
{
    public function indexAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            $this->get('session')->getFlashBag()->add('warning', 'Please login or register');
            $this->get('session')->set('redirect_url', $this->generateUrl('cart_checkout', []));
            return $this->redirect($this->generateUrl('login_route', []));
        }

        // is checkout multiple pages or a single page
        if ($this->container->getParameter('cart.checkout.spa.enabled')) {

            return $this->get('cart.checkout.form')
                ->setRequest($request)
                ->setUser($this->getUser())
                ->getFullResponse();

        }

        return $this->get('cart.checkout.form')
            ->setRequest($request)
            ->setUser($this->getUser())
            ->getSectionResponse($this->get('cart.checkout.form')->getFirstSectionKey());
    }

    public function viewSectionAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            $this->get('session')->getFlashBag()->add('warning', 'Please login or register');
            $this->get('session')->set('redirect_url', $this->generateUrl('cart_checkout', []));
            return $this->redirect($this->generateUrl('login_route', []));
        }

        $section = $request->get('section', '');
        switch($section) {
            case 'confirm_order':
                return $this->confirmOrderAction($request);
                break;
            case 'success':
                return $this->successAction($request);
                break;
            default:

                break;
        }

        return $this->get('cart.checkout.form')
            ->setRequest($request)
            ->setUser($this->getUser())
            ->getSectionResponse($section);
    }

    public function updateSectionAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            $this->get('session')->getFlashBag()->add('warning', 'Please login or register');
            $this->get('session')->set('redirect_url', $this->generateUrl('cart_checkout', []));
            return $this->redirect($this->generateUrl('login_route', []));
        }

        $section = $request->get('section', '');
        $sectionData = $this->get('cart.checkout.form')
            ->setRequest($request)
            ->setUser($this->getUser())
            ->getSectionData($section);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser())
            ->set('section_data', $sectionData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::checkoutUpdate($section), $event);

        return $event->getResponse();
    }

    public function confirmOrderAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            $this->get('session')->getFlashBag()->add('warning', 'Please login or register');
            $this->get('session')->set('redirect_url', $this->generateUrl('cart_checkout', []));
            return $this->redirect($this->generateUrl('login_route', []));
        }

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_CONFIRM_ORDER, $event);

        return $event->getResponse();
    }

    public function submitOrderAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            $this->get('session')->getFlashBag()->add('warning', 'Please login or register');
            $this->get('session')->set('redirect_url', $this->generateUrl('cart_checkout', []));
            return $this->redirect($this->generateUrl('login_route', []));
        }

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setFormAction($this->generateUrl('cart_checkout_submit_order'))
            ->setFormMethod('POST')
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUBMIT_ORDER, $event);

        return $event->getResponse();
    }

    public function successAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            $this->get('session')->getFlashBag()->add('warning', 'Please login or register');
            $this->get('session')->set('redirect_url', $this->generateUrl('cart_checkout', []));
            return $this->redirect($this->generateUrl('login_route', []));
        }

        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUCCESS_RETURN, $event);

        return $event->getResponse();
    }
}
