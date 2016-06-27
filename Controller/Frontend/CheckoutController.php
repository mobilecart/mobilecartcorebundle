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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CheckoutController extends Controller
{
    /**
     * @Route("/cart/checkout", name="cart_checkout")
     * @Method("GET")
     */
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
        $event->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setUser($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_FORM, $event);

        $form = $event->getForm();
        
        // cases:
        //     anonymous / new customer:
        //         save as new user needing activation, email customer
        //         save new customer
        //         add order to history
        //     anonymous / existing non-activated customer:
        //         force activation OR change email/info
        //     anonymous / registered customer:
        //         force login or registration
        //     logged-in:
        //         add order to customer history

        $returnData = array_merge(
            $event->getReturnData(), [
            'form' => $form->createView(),
        ]);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_VIEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/checkout/update/billing_address", name="cart_checkout_update_billing_address")
     * @Method("POST")
     */
    public function updateBillingAddressAction(Request $request)
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

    /**
     * @Route("/cart/checkout/update/shipping_address", name="cart_checkout_update_shipping_address")
     * @Method("POST")
     */
    public function updateShippingAddressAction(Request $request)
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

    /**
     * @Route("/cart/checkout/update/shipping_method", name="cart_checkout_update_shipping_method")
     * @Method("POST")
     */
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

    /**
     * @Route("/cart/checkout/totals_discounts", name="cart_checkout_totals_discounts")
     * @Method("GET")
     */
    public function totalsDiscountsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_TOTALS_DISCOUNTS, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/checkout/confirm_order", name="cart_checkout_confirm_order")
     * @Method("GET")
     */
    public function confirmOrderAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_CONFIRM_ORDER, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/checkout/update/discount", name="cart_checkout_update_discount")
     * @Method("POST")
     */
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

    /**
     * @Route("/cart/checkout/update/payment", name="cart_checkout_update_payment")
     * @Method("POST")
     */
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

        // build Checkout form, but only want shipping address step
        $formEvent = new CoreEvent();
        $formEvent->setRequest($request)
            ->setAction($this->generateUrl('cart_checkout_submit_order'))
            ->setMethod('POST')
            ->setSingleStep(CheckoutConstants::STEP_PAYMENT_METHODS);

        $form = $formEvent->getForm();

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setForm($form);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_UPDATE_PAYMENT_METHOD, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/cart/checkout/summary", name="cart_checkout_summary")
     * @Method("GET")
     */
    public function orderSummaryAction(Request $request)
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
            ->dispatch(CoreEvents::CHECKOUT_ORDER_SUMMARY, $event);

        return $event->getResponse();
    }

    // todo : action for completing paypal orders, should live in its own module

    /**
     * Handle the complete posted data
     *
     * @Route("/cart/checkout/post", name="cart_checkout_submit_order")
     * @Method("POST")
     */
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

        // cases:
        //     anonymous / new customer:
        //         save as new user needing activation, email customer
        //         save new customer
        //         add order to history
        //     anonymous / existing non-activated customer:
        //         force activation OR change email/info
        //     anonymous / registered customer:
        //         force login or registration
        //     logged-in:
        //         add order to customer history

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setForm($form)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUBMIT_ORDER, $event);

        return $event->getResponse();
    }
    
    /**
     * Show success/confirmation page
     *
     * @Route("/cart/checkout/success", name="cart_checkout_success")
     */
    public function successAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CHECKOUT_SUCCESS_RETURN, $event);

        return $event->getResponse();
    }
    
}
