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

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MobileCart\CoreBundle\Entity\ShippingMethod;
use MobileCart\CoreBundle\Form\ShippingMethodType;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Shipping\RateRequest;

/**
 * ShippingMethod controller.
 *
 * @Route("/admin/shipping_method")
 */
class ShippingMethodController extends Controller
{

    protected $objectType = EntityConstants::SHIPPING_METHOD;

    /**
     * @return array
     */
    protected function getFormSections()
    {
        return [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'title', 'company', 'method', 'price', 'min_days', 'max_days',
                    'is_taxable', 'is_discountable', 'is_price_dynamic', 'pre_conditions',
                ],
            ],
        ];
    }

    /**
     * Get relevant vars for discount editing
     *
     * @return array
     */
    public function getDiscountVars()
    {
        return [
            'qty' => ['datatype' => 'number', 'name' => 'Quantity'],
            'id' => ['datatype' => 'number', 'name' => 'ID'],
            'sku' => ['datatype' => 'string', 'name' => 'SKU'],
            'price' => ['datatype' => 'number', 'name' => 'Price'],
            'weight' => ['datatype' => 'number', 'name' => 'Weight'],
            'category_ids_csv' => ['datatype' => 'string', 'name' => 'Category ID\'s'],
        ];
    }

    /**
     * Lists all ShippingMethod entities.
     *
     * @Route("/", name="cart_admin_shipping_method")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        // Data for Template, etc
        $returnData = [];

        // Observe Event :
        //  populate grid columns and mass actions,
        //  continue building return data

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_LIST, $event);

        $returnData = $event->getReturnData();
        $search = $returnData['search'];

        switch($search->getFormat()) {
            case 'json':
                return new JsonResponse($returnData);
                break;
            default:
                $tplPath = $this->get('cart.theme')->getTemplatePath('admin');
                $view = $tplPath . 'ShippingMethod:index.html.twig';
                return $this->render($view, $returnData);
                break;
        }
    }

    /**
     * Creates a new ShippingMethod entity.
     *
     * @Route("/", name="cart_admin_shipping_method_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_shipping_method_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $formEvent);

        $form = $formEvent->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            // observe event
            //  add item_var to indexes, etc
            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request)
                ->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_INSERT, $event);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Shipping Method Successfully Created!'
            );

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_CREATE_RETURN, $returnEvent);

            return $returnEvent->getResponse();
        }

        if ($request->get('format', '') == 'json') {

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

        $returnData = [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($returnData);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new ShippingMethod entity.
     *
     * @Route("/new", name="cart_admin_shipping_method_new")
     * @Method("GET")
     */
    public function newAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_shipping_method_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a ShippingMethod entity.
     *
     * @Route("/{id}", name="cart_admin_shipping_method_show")
     * @Method("GET")
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
     * Displays a form to edit an existing ShippingMethod entity.
     *
     * @Route("/{id}/edit", name="cart_admin_shipping_method_edit")
     * @Method("GET")
     */
    public function editAction(Request $request, $id)
    {
        $rateRequest = new RateRequest();
        $rateRequest->set('include_all', 1);

        $entity = is_numeric($id)
            ? $this->get('cart.entity')->find($this->objectType, $id)
            : $this->get('cart.shipping')->getShippingMethod($rateRequest, $id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ShippingMethod entity.');
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_shipping_method_update', ['id' => $entity->getId()]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_EDIT_RETURN, $event);

        return $event->getResponse();
    }


    /**
     * Edits an existing ShippingMethod entity.
     *
     * @Route("/{id}", name="cart_admin_shipping_method_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $rateRequest = new RateRequest();
        $rateRequest->set('include_all', 1);

        $entity = is_numeric($id)
            ? $this->get('cart.entity')->find($this->objectType, $id)
            : $this->get('cart.shipping')->getShippingMethod($rateRequest, $id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ShippingMethod entity.');
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_shipping_method_update', ['id' => $entity->getId()]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::SHIPPING_METHOD_ADMIN_FORM, $formEvent);

        $form = $formEvent->getForm();
        if ($form->handleRequest($request)->isValid()) {

            if (is_numeric($id)) {

                $formData = $request->request->get($form->getName());

                // observe event
                //  add item_var to indexes, etc
                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request)
                    ->setFormData($formData);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::SHIPPING_METHOD_UPDATE, $event);

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Shipping Method Successfully Updated!'
                );

            } else {

                $formData = $request->request->get($form->getName());
                $entity->setId(null);

                // observe event
                //  add item_var to indexes, etc
                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request)
                    ->setFormData($formData);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::SHIPPING_METHOD_INSERT, $event);

                $request->getSession()->getFlashBag()->add(
                    'success',
                    'Shipping Method Successfully Updated!'
                );

            }

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::SHIPPING_METHOD_UPDATE_RETURN, $returnEvent);

            return $returnEvent->getResponse();
        }

        if ($request->get('format', '') == 'json') {

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
            ->dispatch(CoreEvents::SHIPPING_METHOD_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a ShippingMethod entity.
     *
     * @Route("/{id}", name="cart_admin_shipping_method_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);

        if ($form->handleRequest($request)->isValid()) {

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
     *
     * @Route("/mass_delete", name="cart_admin_shipping_method_mass_delete")
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
