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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class OrderController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::ORDER;

    /**
     * Lists Order entities.
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType(EntityConstants::ORDER)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new Order entity.
     */
    public function createAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setUser($this->getUser())
            ->setSection(CoreEvent::SECTION_BACKEND)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_order_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $event);

        $invalid = [];
        if ($event->isFormValid()) {

            $email = $request->get('customer_email', '');

            // more validation
            switch($request->get('customer_strategy', '')) {
                case 'existing':
                    $customerId = (int) $request->get('customer_id', 0);
                    if ($customerId) {
                        $customer = $this->get('cart.entity')->find(EntityConstants::CUSTOMER, $customerId);
                        if ($customer) {
                            $event->getEntity()->setCustomer($customer);
                            $event->getEntity()->setEmail($customer->getEmail());
                        } else {
                            $invalid['customer_id'] = ['Customer does not exist'];
                        }
                    } else {
                        $invalid['customer_id'] = ['Customer ID cannot be zero'];
                    }

                    break;
                case 'guest':

                    if (strlen($email) < 5) {
                        $invalid['customer_email'] = ['Invalid email address'];
                    } else {
                        $event->getEntity()->setEmail($email);
                    }

                    break;
                case 'new':

                    // create customer
                    $plaintext = $request->get('customer_password', '');
                    $plaintextConfirm = $request->get('customer_password_confirm', '');
                    if (strlen($plaintext) > 0) {
                        if ($plaintext != $plaintextConfirm) {
                            $invalid['customer_password'] = ['Passwords do not match'];
                            $invalid['customer_password_confirm'] = ['Passwords do not match'];
                        }
                    } else {
                        $invalid['customer_password'] = ['Password cannot be blank'];
                        $invalid['customer_password_confirm'] = ['Password cannot be blank'];
                    }

                    break;
                default:

                    break;
            }

            if (!$invalid) {

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::ORDER_INSERT, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::ORDER_CREATE_RETURN, $event);

                return $event->getResponse();
            }
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse($invalid);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new Order entity.
     */
    public function newAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_order_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a Order entity.
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
     * Displays a form to edit an existing Order entity.
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
            ->setFormAction($this->generateUrl('cart_admin_order_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Order entity.
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
            ->setUser($this->getUser())
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_order_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Update Shipping Method
     */
    public function updateShippingAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_UPDATE_SHIPPING, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Update Items
     */
    public function updateItemsAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_UPDATE_ITEMS, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Add Item
     */
    public function addItemAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADD_ITEM, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Remove Item
     */
    public function removeItemAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_REMOVE_ITEM, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Add Discount
     */
    public function addDiscountAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADD_DISCOUNT, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Remove Discount
     */
    public function removeDiscountAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_REMOVE_ITEM, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Update Customer
     */
    public function updateCustomerAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_UPDATE_CUSTOMER, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Deletes a Order entity.
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Order entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_DELETE, $event);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Order Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_order'));
    }

    /**
     * Mass-Delete Orders
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
                    ->dispatch(CoreEvents::ORDER_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' Orders Successfully Deleted'
            );
        }

        return new JsonResponse($returnData);
    }

    /**
     * Creates a form to delete an entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart_admin_order_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }
}
