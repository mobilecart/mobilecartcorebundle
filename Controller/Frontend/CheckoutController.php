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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class CheckoutController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class CheckoutController extends Controller
{
    /**
     * @return bool
     */
    protected function hasLoginError()
    {
        return (!$this->get('cart.checkout.session')->getAllowGuestCheckout() && !$this->getUser());
    }

    /**
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function handleLoginError(Request $request)
    {
        switch(true) {
            case $request->headers->get('Accept') == 'application/json':
            case $request->headers->get('Content-Type') == 'application/json':

                return new JsonResponse([
                    'success' => false,
                    'messages' => [
                        'error' => [
                            'Please login or register'
                        ]
                    ]
                ], 401);

                break;
            default:

                $this->get('session')->getFlashBag()->add('warning', 'Please login or register');
                $this->get('session')->set('redirect_url', $this->generateUrl('cart_checkout', []));
                return $this->redirect($this->generateUrl('login_route', []));
                break;
        }
    }

    /**
     * View the first step of the checkout process, or all steps at once
     */
    public function indexAction(Request $request)
    {
        // check if login/registration is required
        if ($this->hasLoginError()) {
            return $this->handleLoginError($request);
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

    /**
     * View a specific step of the checkout process
     */
    public function viewSectionAction(Request $request)
    {
        // check if login/registration is required
        if ($this->hasLoginError()) {
            return $this->handleLoginError($request);
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

    /**
     * Update a specific step of the checkout process
     */
    public function updateSectionAction(Request $request)
    {
        // check if login/registration is required
        if ($this->hasLoginError()) {
            return $this->handleLoginError($request);
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

    /**
     * Confirm the order summary
     * todo : move this logic into a "template handler" in the cart view listener, and remove this
     */
    public function confirmOrderAction(Request $request)
    {
        // check if login/registration is required
        if ($this->hasLoginError()) {
            return $this->handleLoginError($request);
        }

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_CONFIRM_ORDER, $event);

        return $event->getResponse();
    }

    /**
     * Submit the order
     */
    public function submitOrderAction(Request $request)
    {
        // check if login/registration is required
        if ($this->hasLoginError()) {
            return $this->handleLoginError($request);
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

    /**
     * Submit the order via API
     */
    public function submitOrderApiAction(Request $request)
    {
        // check if login/registration is required
        if ($this->hasLoginError()) {
            return $this->handleLoginError($request);
        }

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUBMIT_ORDER_API, $event);

        return $event->getResponse();
    }

    /**
     * View the order success page
     */
    public function successAction(Request $request)
    {
        // check if login/registration is required
        if ($this->hasLoginError()) {
            return $this->handleLoginError($request);
        }

        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUCCESS_RETURN, $event);

        return $event->getResponse();
    }
}
