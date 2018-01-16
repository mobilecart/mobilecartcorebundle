<?php

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderPaymentController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class OrderPaymentController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::ORDER_PAYMENT;

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
            ->dispatch(CoreEvents::ORDER_PAYMENT_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new OrderPayment entity.
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
            ->setFormAction($this->generateUrl('cart_admin_order_payment_create', ['order_id' => $order_id]))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_PAYMENT_ADMIN_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_PAYMENT_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_PAYMENT_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->getRequestAccept() == CoreEvent::JSON) {

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
            ->dispatch(CoreEvents::ORDER_PAYMENT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new OrderPayment entity.
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
            ->setFormAction($this->generateUrl('cart_admin_order_payment_create', ['order_id' => $order_id]))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_PAYMENT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_PAYMENT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a OrderPayment entity.
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
     * Displays a form to edit an existing OrderPayment entity.
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
            ->setFormAction($this->generateUrl('cart_admin_order_payment_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_PAYMENT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_PAYMENT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing OrderPayment entity.
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
            ->setFormAction($this->generateUrl('cart_admin_order_payment_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_PAYMENT_ADMIN_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_PAYMENT_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_PAYMENT_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->getRequestAccept() == CoreEvent::JSON) {

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
            ->dispatch(CoreEvents::ORDER_PAYMENT_EDIT_RETURN, $event);

        return $event->getResponse();
    }
}
