<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Entity\ShippingMethod;
use MobileCart\CoreBundle\Form\ShippingMethodType;
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Shipping\RateRequest;

/**
 * Class ShippingMethodController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class ShippingMethodController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::SHIPPING_METHOD;

    /**
     * Lists ShippingMethod entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new ShippingMethod entity
     */
    public function createAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_shipping_method_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new ShippingMethod entity
     */
    public function newAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_shipping_method_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a ShippingMethod entity
     */
    public function showAction($id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        return new JsonResponse($entity->getData());
    }

    /**
     * Displays a form to edit an existing ShippingMethod entity
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ShippingMethod entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_shipping_method_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing ShippingMethod entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ShippingMethod entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_shipping_method_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a ShippingMethod entity
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        if ($form->isValid()) {

            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ShippingMethod entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_DELETE, $event);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Shipping Method Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_shipping_method'));
    }

    /**
     * Creates a form to delete a ShippingMethod entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart_admin_shipping_method_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm()
        ;
    }

    /**
     * Mass-Delete
     */
    public function massDeleteAction(Request $request)
    {
        $itemIds = $request->get('item_ids', []);
        $returnData = ['item_ids' => []];

        if ($itemIds) {
            foreach($itemIds as $itemId) {
                $entity = $this->get('cart.entity')->find($this->objectType, $itemId);
                if (!$entity) {
                    $returnData['error'][] = $itemId;
                    continue;
                }

                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::SHIPPING_METHOD_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' Shipping Methods Successfully Deleted'
            );
        }

        return new JsonResponse($returnData);
    }
}
