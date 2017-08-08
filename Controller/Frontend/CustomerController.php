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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CustomerController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class CustomerController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CUSTOMER;

    /**
     * Display registration form
     */
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

    /**
     * Handle registration form submission
     */
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

            $event->setFormData($request->request->get($form->getName()));

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

    /**
     * Inform customer to check their email for a confirmation link
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
     * Validate confirmation link and inform customer of status
     */
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

    /**
     * Display forgot password form
     */
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

    /**
     * Handle forgot password form submission
     */
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

        $event->setReturnData('error', true);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Handle forgot password form submission
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
     * Display update password form
     */
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

    /**
     * Handle update password form submission
     */
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

                $event->setFormData([
                    'password' => $form->get('password')->getData(),
                ]);

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

    /**
     * Display profile form
     */
    public function profileAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setEntity($this->getUser())
            ->setCurrentRoute('customer_profile');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Handle profile form submission
     */
    public function updateAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setEntity($this->getUser())
            ->setSection(CoreEvent::SECTION_FRONTEND)
            ->setCurrentRoute('customer_profile');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $event->setFormData($request->request->get($form->getName()));

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE, $event);

            $event->setIsValid(true);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_PROFILE_POST_RETURN, $event);

            return $event->getResponse();
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

            $invalid = [];
            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    $invalid[$childKey] = [];
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }

            return new JsonResponse([
                'success' => false,
                'invalid' => $invalid,
                'messages' => $event->getMessages(),
            ]);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_PROFILE_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * List customers order history
     */
    public function orderHistoryAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setCustomer($this->getUser())
            ->setCurrentRoute('customer_orders');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ORDERS_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * View customer order
     */
    public function orderViewAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setCustomer($this->getUser())
            ->setCurrentRoute('customer_profile');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ORDER_RETURN, $event);

        return $event->getResponse();
    }
}
