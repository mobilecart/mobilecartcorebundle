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
 * Class CustomerAddressController
 * @package MobileCart\CoreBundle\Controller\Frontend
 */
class CustomerAddressController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CUSTOMER_ADDRESS;

    /**
     * List and search CustomerAddress entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_FRONTEND)
            ->setUser($this->getUser())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Display Form for CustomerAddress
     */
    public function newAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('customer_address_create'))
            ->setFormMethod('POST')
            ->setSection(CoreEvent::SECTION_FRONTEND)
            ->setCustomer($this->getUser())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Handle Form Submission for CustomerAddress
     */
    public function createAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('customer_address_create'))
            ->setFormMethod('POST')
            ->setSection(CoreEvent::SECTION_FRONTEND)
            ->setCustomer($this->getUser())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Display Form for CustomerAddress
     */
    public function editAction(Request $request)
    {
        $addressId = (int) $request->get('id', 0);

        $entity = $this->get('cart.entity')->findOneBy($this->objectType, [
            'id' => $addressId,
            'customer' => $this->getUser(),
        ]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Address');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('customer_address_update', ['id' => $addressId]))
            ->setFormMethod('PUT')
            ->setSection(CoreEvent::SECTION_FRONTEND)
            ->setCustomer($this->getUser())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Display Form for CustomerAddress
     */
    public function updateAction(Request $request)
    {
        $addressId = (int) $request->get('id', 0);

        $entity = $this->get('cart.entity')->findOneBy($this->objectType, [
            'id' => $addressId,
            'customer' => $this->getUser(),
        ]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Address');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('customer_address_update', ['id' => $addressId]))
            ->setFormMethod('PUT')
            ->setSection(CoreEvent::SECTION_FRONTEND)
            ->setCustomer($this->getUser())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Handle Delete for CustomerAddress
     */
    public function deleteAction(Request $request)
    {
        $addressId = $request->get('id', 0);

        $entity = $this->get('cart.entity')->findOneBy($this->objectType, [
            'id' => $addressId,
            'customer' => $this->getUser(),
        ]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Address');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_DELETE, $event);

        return $event->getResponse();
    }
}
