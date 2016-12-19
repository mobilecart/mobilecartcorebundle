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
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CheckoutController extends Controller
{
    public function indexAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            // todo : add flash message
            // $this->get('session')->getFlashBag()->add($code, $message);

            $url = $this->generateUrl('cart_checkout', []);
            $this->get('session')->set('redirect_url', $url);

            return $this->redirect($this->generateUrl('login_route', []));
        }

        $event = new CoreEvent();

        // is checkout multiple pages or a single page
        if ($this->container->getParameter('cart.checkout.spa.enabled')) {

            $formEvent = new CoreEvent();
            $formEvent->setRequest($request)
                ->setAction($this->generateUrl('cart_checkout_submit_order'))
                ->setMethod('POST')
                ->setUser($this->getUser());

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

            $form = $formEvent->getForm();

        } else {

            // display billing address form if it's not single page app

            $formEvent = new CoreEvent();
            $formEvent->setRequest($request)
                ->setAction($this->generateUrl('cart_checkout_update_billing_address'))
                ->setMethod('POST')
                ->setSingleStep(CheckoutConstants::STEP_BILLING_ADDRESS);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

            $formType = $formEvent->getBillingAddressForm();

            // todo : stuff this logic into a listener, set a flag for single-page-app enabled
            $form = $this->createForm($formType);
            $returnData = $formEvent->getReturnData();

            $sections = isset($returnData['sections'])
                ? $returnData['sections']
                : [];

            $cartSession = $this->get('cart.session');
            $cart = $cartSession->getCart();
            $customer = $cart->getCustomer();

            foreach($sections as $section => $sectionData) {
                if (!isset($sections[$section]['fields'])) {
                    continue;
                }
                foreach($sections[$section]['fields'] as $field) {
                    if ($customerValue = $customer->get($field)) {
                        if ($field == 'is_shipping_same') { // symfony Form requires a boolean now, wont take a '1'
                            $form->get($field)->setData((bool) $customerValue);
                        } else {
                            $form->get($field)->setData($customerValue);
                        }
                    }
                }
            }

            $event->setSingleStep(CheckoutConstants::STEP_BILLING_ADDRESS)
                ->setStepNumber(1);
        }

        $returnData = array_merge(
            $formEvent->getReturnData(), [
            'form' => $form->createView(),
        ]);

        $event->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    public function shippingAddressAction(Request $request)
    {
        $isSpaEnabled = $this->container->getParameter('cart.checkout.spa.enabled');
        if ($isSpaEnabled) {
            return $this->redirect($this->generateUrl('cart_checkout'));
        }

        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_update_shipping_address'))
            ->setMethod('POST')
            ->setSingleStep(CheckoutConstants::STEP_SHIPPING_ADDRESS);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

        $formType = $formEvent->getShippingAddressForm();
        $form = $this->createForm($formType);

        $returnData = array_merge(
            $formEvent->getReturnData(), [
            'form' => $form->createView(),
        ]);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setReturnData($returnData)
            ->setSingleStep(CheckoutConstants::STEP_SHIPPING_ADDRESS)
            ->setStepNumber(2);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    public function totalsDiscountsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        if (!$this->container->getParameter('cart.checkout.spa.enabled')) {

            $stepNumber = $this->container->getParameter('cart.shipping.enabled')
                ? 3
                : 2;

            $event->setSingleStep(CheckoutConstants::STEP_TOTALS_DISCOUNTS)
                ->setStepNumber($stepNumber);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_TOTALS_DISCOUNTS, $event);

        return $event->getResponse();
    }

    public function paymentMethodsAction(Request $request)
    {
        $isSpaEnabled = $this->container->getParameter('cart.checkout.spa.enabled');
        if ($isSpaEnabled || !$this->get('cart.session')->hasItems()) {
            return $this->redirect($this->generateUrl('cart_checkout'));
        }

        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {

            return new JsonResponse([
                'success' => 0,
                'errors' => [
                    'Please login or register'
                ]
            ]);
        }

        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

        $form = $formEvent->getForm();
        $returnData = $formEvent->getReturnData();

        $stepNumber = $this->container->getParameter('cart.shipping.enabled')
            ? 4
            : 3;

        $returnData = array_merge(
            $returnData,
            $formEvent->getReturnData(), [
                'form' => $form->createView(),
            ]);

        $viewEvent = new CoreEvent();
        $viewEvent->setRequest($request)
            ->setReturnData($returnData)
            ->setDisableRender(1)
            ->setStepNumber($stepNumber)
            ->setSingleStep(CheckoutConstants::STEP_PAYMENT_METHODS)
        ;

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_VIEW_RETURN, $viewEvent);

        $returnData = array_merge($viewEvent->getReturnData(), $returnData);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_PAYMENT_METHODS_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    public function confirmOrderAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_CONFIRM_ORDER, $event);

        return $event->getResponse();
    }

    public function submitOrderAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            return new JsonResponse([
                'success' => 0,
                'errors' => [
                    'Please login or register'
                ]
            ]);
        }

        // todo : keep a count of invalid requests, logout/lockout user if excessive

        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

        $form = $formEvent->getForm();

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setForm($form)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUBMIT_ORDER, $event);

        return $event->getResponse();
    }

    public function successAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUCCESS_RETURN, $event);

        return $event->getResponse();
    }

    public function updateBillingAddressAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {

            if (!$this->container->getParameter('cart.checkout.spa.enabled')) {
                // todo : flash message
                return $this->redirectToRoute('cart_checkout');
            }

            return new JsonResponse([
                'success' => 0,
                'errors' => [
                    'Please login or register'
                ]
            ]);
        }

        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setSingleStep(CheckoutConstants::STEP_BILLING_ADDRESS);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

        $form = $formEvent->getBillingAddressForm();

        $entity = $this->get('cart.entity')->getInstance(EntityConstants::ORDER);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setForm($form)
            ->setAction($this->generateUrl('cart_checkout_update_billing_address'))
            ->setMethod('POST')
            ->setEntity($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_UPDATE_BILLING_ADDRESS, $event);

        return $event->getResponse();
    }

    public function updateShippingAddressAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {

            if (!$this->container->getParameter('cart.checkout.spa.enabled')) {
                // todo : flash message
                return $this->redirectToRoute('cart_checkout');
            }

            return new JsonResponse([
                'success' => 0,
                'errors' => [
                    'Please login or register'
                ]
            ]);
        }

        // build Checkout form, but only want shipping address step
        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setSingleStep(CheckoutConstants::STEP_SHIPPING_ADDRESS);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

        $form = $formEvent->getShippingAddressForm();

        $entity = $this->get('cart.entity')->getInstance(EntityConstants::ORDER);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setForm($form)
            ->setAction($this->generateUrl('cart_checkout_update_shipping_address'))
            ->setMethod('POST')
            ->setEntity($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_UPDATE_SHIPPING_ADDRESS, $event);

        return $event->getResponse();
    }

    public function updateShippingMethodAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {

            return new JsonResponse([
                'success' => 0,
                'errors' => [
                    'Please login or register'
                ]
            ]);
        }

        // build Checkout form, but only want shipping method step
        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setSingleStep(CheckoutConstants::STEP_SHIPPING_METHOD);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

        $form = $formEvent->getShippingMethodForm();

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setForm($form)
            ->setAction($this->generateUrl('cart_checkout_update_shipping_method', []))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_UPDATE_SHIPPING_METHOD, $event);

        return $event->getResponse();
    }

    public function updateTotalsDiscountsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_UPDATE_TOTALS_DISCOUNTS, $event);

        return $event->getResponse();
    }

    public function updateDiscountAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {
            return new JsonResponse([
                'success' => 0,
                'errors' => [
                    'Please login or register'
                ]
            ]);
        }

        // build Checkout form, but only want shipping address step
        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setSingleStep(CheckoutConstants::STEP_TOTALS_DISCOUNTS);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $formEvent);

        $form = $formEvent->getForm();

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setForm($form);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_UPDATE_TOTALS_DISCOUNTS, $event);

        return $event->getResponse();
    }

    public function updatePaymentMethodAction(Request $request)
    {
        // check if login/registration is required
        $checkoutService = $this->get('cart.checkout.session');
        if (!$checkoutService->getAllowGuestCheckout() && !$this->getUser()) {

            return new JsonResponse([
                'success' => 0,
                'errors' => [
                    'Please login or register'
                ]
            ]);
        }

        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_UPDATE_PAYMENT_METHOD, $event);

        return $event->getResponse();
    }
}
