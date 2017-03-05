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
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CustomerController extends Controller
{
    protected $objectType = EntityConstants::CUSTOMER;

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

    public function registerCheckEmailAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_CHECK_EMAIL_RETURN, $event);

        return $event->getResponse();
    }

    public function registerConfirmAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_CONFIRM, $event);

        $entity = $event->getEntity();
        $success = $event->getSuccess();

        $returnEvent = new CoreEvent();
        $returnEvent->setObjectType($this->objectType)
            ->setRequest($request)
            ->setSuccess($success)
            ->setEntity($entity)
            ->setReturnData($event->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_CONFIRM_RETURN, $returnEvent);

        return $returnEvent->getResponse();
    }

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

        $returnEvent = new CoreEvent();
        $returnEvent->setObjectType($this->objectType)
            ->setReturnData($event->getReturnData())
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_RETURN, $returnEvent);

        return $returnEvent->getResponse();
    }

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
            } else {
                $returnData['error'] = 1;
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

    public function forgotPasswordSuccessAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_SUCCESS, $event);

        return $event->getResponse();
    }

    public function updatePasswordAction(Request $request)
    {
        // todo
    }

    public function updatePassswordPostAction(Request $request)
    {
        // todo
    }

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
                ->setFormData($formData)
                ->setSection(CoreEvent::SECTION_FRONTEND);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE, $event);

            $nav = new CoreEvent();
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setReturnData(array_merge(['form' => $form->createView()], $nav->getReturnData()))
                ->setEntity($entity)
                ->setRequest($request)
                ->setIsValid(1);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_PROFILE_POST_RETURN, $event);

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
            ->setReturnData($returnData)
            ->setIsValid(0)
            ->setForm($form);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_RETURN, $event);

        return $event->getResponse();
    }

    public function orderHistoryAction(Request $request)
    {
        $nav = new CoreEvent();
        $nav->setCurrentRoute('customer_orders');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $returnData = $nav->getReturnData();

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData)
            ->setCustomer($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ORDERS_RETURN, $event);

        return $event->getResponse();
    }

    public function orderViewAction(Request $request)
    {
        $nav = new CoreEvent();
        $nav->setCurrentRoute('customer_profile');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $returnData = $nav->getReturnData();

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData)
            ->setCustomer($this->getUser());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ORDER_RETURN, $event);

        return $event->getResponse();
    }
}
