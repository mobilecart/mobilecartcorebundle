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
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CUSTOMER;

    public function registerAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setEntity($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_FORM, $event);

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
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_REGISTER, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_REGISTER_POST_RETURN, $event);

            return $event->getResponse();
        }

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

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_CONFIRM_RETURN, $event);

        return $event->getResponse();
    }

    public function forgotPasswordAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_RETURN, $event);

        return $event->getResponse();
    }

    public function forgotPasswordPostAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $form->getData();
            $email = isset($formData['email']) ? $formData['email'] : '';

            $entity = $this->get('cart.entity')
                ->findOneBy($this->objectType, ['email' => $email]);

            if ($entity) {

                $event->setEntity($entity);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_POST_RETURN, $event);

                return $event->getResponse();
            }
        }

        $event->setReturnData('error', 1);

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
        $customerId = $request->get('id', 0);
        $confirmHash = $request->get('hash', '');

        $entity = $this->get('cart.entity')
            ->findOneBy($this->objectType, [
                'id' => $customerId,
                'confirm_hash' => $confirmHash,
            ]);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setEntity($entity);

        if ($entity) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_FORM, $event);
        } else {
            $event->setReturnData('form', null);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_RETURN, $event);

        return $event->getResponse();
    }

    public function updatePasswordPostAction(Request $request)
    {
        $customerId = $request->get('id', 0);
        $confirmHash = $request->get('hash', '');

        $entity = $this->get('cart.entity')
            ->findOneBy($this->objectType, [
                'id' => $customerId,
                'confirm_hash' => $confirmHash,
            ]);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setEntity($entity);

        if ($entity) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_FORM, $event);

            $form = $event->getReturnData('form');
            if ($form->handleRequest($request)->isValid()) {

                $plaintext = $form->get('password')->getData();
                $formData = [
                    'password' => $plaintext,
                ];

                $event->setFormData($formData);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD, $event);

                $event->setReturnData('success', true);

            } else {
                $event->setReturnData('success', false);
            }
        } else {
            $event->setReturnData('success', false);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_POST_RETURN, $event);

        return $event->getResponse();
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
