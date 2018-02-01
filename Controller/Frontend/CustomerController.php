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
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType));

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
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_REGISTER_FORM, $event);

        if ($event->isFormValid()) {

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

        if ($event->isFormValid()) {

            $entity = $this->get('cart.entity')->findOneBy($this->objectType, [
                'email' => $event->getFormData('email')
            ]);

            if ($entity) {

                $event->setEntity($entity);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_FORGOT_PASSWORD_POST_RETURN, $event);

                return $event->getResponse();
            }
        }

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
        $entity = $this->get('cart.entity')->findOneBy($this->objectType, [
            'id' => $request->get('id', 0),
            'confirm_hash' => $request->get('hash', ''),
            'is_enabled' => true,
        ]);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        if (!$entity) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_FAILED_RETURN, $event);

            return $event->getResponse();
        }

        $event->setEntity($entity);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Handle update password form submission
     */
    public function updatePasswordPostAction(Request $request)
    {
        $entity = $this->get('cart.entity')->findOneBy($this->objectType, [
            'id' => $request->get('id', 0),
            'confirm_hash' => $request->get('hash', ''),
            'is_enabled' => true,
        ]);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $invalid = [];
        if ($entity) {

            $event->setEntity($entity);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_FORM, $event);

            if ($event->isFormValid()) {

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_POST_RETURN, $event);

                return $event->getResponse();

            }
        } else {
            $invalid['email'] = 'Specified email is not in our records';
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse($invalid);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_UPDATE_PASSWORD_RETURN, $event);

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

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_PROFILE_POST_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
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
            ->setCurrentRoute('customer_orders');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ORDER_RETURN, $event);

        return $event->getResponse();
    }
}
