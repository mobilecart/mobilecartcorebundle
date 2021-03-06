<?php

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderShipmentController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class OrderShipmentController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::ORDER_SHIPMENT;

    /**
     * Lists Order entities.
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new OrderShipment entity.
     */
    public function createAction(Request $request, $order_id)
    {
        $order = $this->get('cart.entity')->find(EntityConstants::ORDER, $order_id);
        if (!$order) {
            throw $this->createNotFoundException("Unable to find Order with ID: {$order_id}");
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $entity->setOrder($order);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setSection(CoreEvent::SECTION_BACKEND)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_order_shipment_create', ['order_id' => $order_id]))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_SHIPMENT_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_SHIPMENT_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new OrderShipment entity.
     */
    public function newAction(Request $request, $order_id)
    {
        $order = $this->get('cart.entity')->find(EntityConstants::ORDER, $order_id);
        if (!$order) {
            throw $this->createNotFoundException("Unable to find Order with ID: {$order_id}");
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $entity->setOrder($order);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_order_shipment_create', ['order_id' => $order_id]))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a OrderShipment entity.
     */
    public function showAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        return new JsonResponse($entity->getData());
    }

    /**
     * Displays a form to edit an existing OrderShipment entity.
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_order_shipment_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing OrderShipment entity.
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_order_shipment_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_SHIPMENT_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_SHIPMENT_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SHIPMENT_EDIT_RETURN, $event);

        return $event->getResponse();
    }
}
