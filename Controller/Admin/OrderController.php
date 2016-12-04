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

use MobileCart\CoreBundle\Constants\EntityConstants;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use MobileCart\CoreBundle\Entity\Order;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Order controller.
 *
 * @Route("/admin/order")
 */
class OrderController extends Controller
{

    protected $objectType = EntityConstants::ORDER;

    /**
     * Lists Order entities.
     *
     * @Route("/", name="cart_admin_order")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        // Load a service; which extends Search\SearchAbstract
        // The service parameter is stored in the service configuration as a parameter ; (slightly meta)
        // This service could use either MySQL or ElasticSearch, etc for retrieving item data
        $searchParam = $this->container->getParameter('cart.load.admin');
        $search = $this->container->get($searchParam);

        // Observe Event :
        //  perform custom logic, post-processing

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setSearch($search)
            ->setObjectType(EntityConstants::ORDER)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_SEARCH, $event);

        $search = $event->getSearch();

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_order', $request->getQueryString());
        }

        // Data for Template, etc
        $returnData = [
            'search' => $search,
            'result' => $search->getResult(),
        ];

        // Observe Event :
        //  populate grid columns and mass actions,
        //  continue building return data

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_LIST, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new Order entity.
     *
     * @Route("/", name="cart_admin_order_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $varSet = '';
        if ($varSetId = $request->get('var_set_id', '')) {
            $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        } else {
            $varSets = $this->get('cart.entity')->getVarSets(EntityConstants::ORDER);
            if ($varSets) {
                $varSet = $varSets[0];
            }
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        if ($varSet) {
            $entity->setItemVarSet($varSet);
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_order_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $formEvent);

        $form = $formEvent->getForm();
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            // observe event
            //  add order to indexes, etc
            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setSection(CoreEvent::SECTION_BACKEND)
                ->setRequest($request)
                ->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_INSERT, $event);

            $entity = $event->getOrder();

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_CREATE_RETURN, $returnEvent);

            return $returnEvent->getResponse();
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

            $invalid = [];
            $messages = [];
            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    $invalid[$childKey] = [];
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }

            $returnData = [
                'success' => 0,
                'invalid' => $invalid,
                'messages' => $messages,
            ];

            return new JsonResponse($returnData);
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setEntity($entity)
            ->setVarSet($varSet)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new Order entity.
     *
     * @Route("/new", name="cart_admin_order_new")
     * @Method("GET")
     */
    public function newAction(Request $request)
    {
        $varSet = '';
        if ($varSetId = $request->get('var_set_id', '')) {
            $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        } else {
            $varSets = $this->get('cart.entity')->getVarSets(EntityConstants::ORDER);
            if ($varSets) {
                $varSet = $varSets[0];
            }
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        if ($varSet) {
            $entity->setItemVarSet($varSet);
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_order_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a Order entity.
     *
     * @Route("/{id}", name="cart_admin_order_show")
     * @Method("GET")
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
     *
     * @Route("/{id}/edit", name="cart_admin_order_edit")
     * @Method("GET")
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_order_update', ['id' => $entity->getId()]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Order entity.
     *
     * @Route("/{id}", name="cart_admin_order_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_order_update', ['id' => $entity->getId()]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADMIN_FORM, $formEvent);

        $form = $formEvent->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            // observe event
            // update entity via command bus
            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request)
                ->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ORDER_UPDATE, $event);

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_UPDATE_RETURN, $returnEvent);

            return $returnEvent->getResponse();
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

            $invalid = [];
            $messages = [];
            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    $invalid[$childKey] = [];
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }

            $returnData = [
                'success' => 0,
                'invalid' => $invalid,
                'messages' => $messages,
            ];

            return new JsonResponse($returnData);
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Update Shipping Method
     *
     * @Route("/update/shipping", name="cart_admin_order_shipping")
     * @Method("POST")
     */
    public function updateShippingAction(Request $request)
    {
        // cart json
        // shipping method id/code

        $returnData = [];
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_UPDATE_SHIPPING, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Update Items
     *
     * @Route("/update/items", name="cart_admin_order_items")
     * @Method("POST")
     */
    public function updateItemsAction(Request $request)
    {
        $returnData = [];
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_UPDATE_ITEMS, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Add Item
     *
     * @Route("/add/item", name="cart_admin_order_add_item")
     * @Method("POST")
     */
    public function addItemAction(Request $request)
    {
        $returnData = [];
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADD_ITEM, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Remove Item
     *
     * @Route("/remove/item", name="cart_admin_order_remove_item")
     * @Method("POST")
     */
    public function removeItemAction(Request $request)
    {
        $returnData = [];
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_REMOVE_ITEM, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Add Discount
     *
     * @Route("/add/discount", name="cart_admin_order_add_discount")
     * @Method("POST")
     */
    public function addDiscountAction(Request $request)
    {
        $returnData = [];
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_ADD_DISCOUNT, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Remove Discount
     *
     * @Route("/remove/discount", name="cart_admin_order_remove_discount")
     * @Method("POST")
     */
    public function removeDiscountAction(Request $request)
    {
        $returnData = [];
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_REMOVE_ITEM, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Update Customer
     *
     * @Route("/update/customer", name="cart_admin_order_customer")
     * @Method("POST")
     */
    public function updateCustomerAction(Request $request)
    {
        // cart json
        // shipping method id/code

        $returnData = [];
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ORDER_UPDATE_CUSTOMER, $event);

        return new JsonResponse($event->getReturnData());
    }

    /**
     * Deletes a Order entity.
     *
     * @Route("/{id}", name="cart_admin_order_delete")
     * @Method("DELETE")
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
     *
     * @Route("/mass_delete", name="cart_admin_order_mass_delete")
     * @Method("POST")
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
