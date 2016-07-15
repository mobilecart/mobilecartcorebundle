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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CustomerController extends Controller
{

    protected $objectType = EntityConstants::CUSTOMER;

    /**
     * @Route("/account/register", name="customer_register")
     * @Method("GET")
     */
    public function registerAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_register_post'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_FORM, $event);

        $form = $event->getForm();

        $returnData = array_merge(
            $event->getReturnData(),
            ['form' => $form->createView()]
        );

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/account/create", name="customer_register_post")
     * @Method("POST")
     */
    public function registerPostAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_register_post'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_FORM, $event);

        $form = $event->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request)
                ->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_REGISTER, $event);

            $entity = $event->getEntity();

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_REGISTER_POST_RETURN, $event);

            return $event->getResponse();
        }

        $returnData = array_merge(
            $event->getReturnData(),
            ['form' => $form->createView()]
        );

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/account/check-email", name="customer_check_email")
     * @Method("GET")
     */
    public function registerCheckEmailAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_CHECK_EMAIL_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/account/confirm", name="customer_register_confirm")
     * @Method("GET")
     */
    public function registerConfirmAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_CONFIRM, $event);

        $entity = $event->getEntity();
        $success = $event->getSuccess();

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setSuccess($success)
            ->setEntity($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_CONFIRM_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/account/forgot-password", name="customer_forgot_password")
     * @Method("GET")
     */
    public function forgotPasswordAction(Request $request)
    {
        //check if customer is authenticated, redirect to profile page if they are

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_forgot_password'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_FORM, $event);

        $returnData = $event->getReturnData();

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setReturnData($returnData)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/account/forgot-password", name="customer_forgot_password_post")
     * @Method("POST")
     */
    public function forgotPasswordPostAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_forgot_password'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_FORM, $event);

        $form = $event->getForm();
        $returnData = $event->getReturnData();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $form->getData();
            $email = isset($formData['email']) ? $formData['email'] : '';

            $entity = $this->get('cart.entity')
                ->findOneBy($this->objectType, ['email' => $email]);

            if ($entity) {

                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD, $event);

                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_POST_RETURN, $event);

                return $event->getResponse();
            }
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setReturnData($returnData)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/account/forgot-password/success", name="customer_forgot_password_success")
     * @Method("GET")
     */
    public function forgotPasswordSuccessAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_SUCCESS, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/customer/profile", name="customer_profile")
     * @Method("GET")
     */
    public function profileAction(Request $request)
    {
        $entity = $this->getUser();

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_update'))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_FORM, $event);

        $form = $event->getForm();

        $nav = new CoreEvent();
        $nav->setReturnData($event->getReturnData())
            ->setCurrentRoute('customer_profile');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $returnData = array_merge(
            $event->getReturnData(),
            $nav->getReturnData(),
            ['form' => $form->createView()]
        );

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/customer/update", name="customer_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request)
    {
        $entity = $this->getUser();

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_update'))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_FORM, $event);

        $form = $event->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request)
                ->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE, $event);

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setReturnData(['form' => $form->createView()])
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_PROFILE_RETURN, $event);

            return $event->getResponse();
        }

        $nav = new CoreEvent();
        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $returnData = array_merge(
            $event->getReturnData(),
            $nav->getReturnData(),
            ['form' => $form->createView()]
        );

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/customer/orders", name="customer_orders")
     * @Method("GET")
     */
    public function orderHistoryAction(Request $request)
    {
        $entity = $this->getUser();

        $nav = new CoreEvent();
        $nav->setCurrentRoute('customer_profile');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $returnData = $nav->getReturnData();

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData)
            ->setCustomer($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ORDERS_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * @Route("/customer/order/{orderId}", name="customer_order")
     * @Method("GET")
     */
    public function orderViewAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ORDER_RETURN, $event);

        return $event->getResponse();
    }
}
