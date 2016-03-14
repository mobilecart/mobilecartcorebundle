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

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * ItemVarOption controller.
 *
 * @Route("/admin/item_var_option")
 */
class ItemVarOptionController extends Controller
{

    protected $objectType = EntityConstants::ITEM_VAR_OPTION;
    protected $dataType = EntityConstants::VARCHAR;

    /**
     * Handling multiple entities/object type's the same way
     *
     * @param $datatype
     */
    protected function initObjectType($datatype)
    {
        switch($datatype) {
            case EntityConstants::DATETIME:
                $this->objectType = EntityConstants::ITEM_VAR_OPTION_DATETIME;
                $this->dataType = $datatype;
                break;
            case EntityConstants::DECIMAL:
                $this->objectType = EntityConstants::ITEM_VAR_OPTION_DECIMAL;
                $this->dataType = $datatype;
                break;
            case EntityConstants::INT:
                $this->objectType = EntityConstants::ITEM_VAR_OPTION_INT;
                $this->dataType = $datatype;
                break;
            case EntityConstants::TEXT:
                $this->objectType = EntityConstants::ITEM_VAR_OPTION_TEXT;
                $this->dataType = $datatype;
                break;
            case EntityConstants::VARCHAR:
                $this->objectType = EntityConstants::ITEM_VAR_OPTION_VARCHAR;
                $this->dataType = $datatype;
                break;
            default:
                $this->objectType = EntityConstants::ITEM_VAR_OPTION_VARCHAR;
                $this->dataType = EntityConstants::VARCHAR;
                break;
        }
    }

    /**
     * Lists ItemVarOption entities.
     *
     * @Route("/", name="cart_admin_item_var_option")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $this->initObjectType($request->get('datatype', ''));

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
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_SEARCH, $event);

        $search = $event->getSearch();

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_item_var_option', $request->getQueryString());
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
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_LIST, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new ItemVarOption entity.
     *
     * @Route("/", name="cart_admin_item_var_option_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->getVarOptionInstance($this->dataType);
        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_item_var_option_create', ['datatype' => $this->dataType]))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $formEvent);

        $form = $formEvent->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            // observe event
            //  add item_var_option to indexes, etc
            $event = new CoreEvent();
            $event->setEntity($entity)
                ->setRequest($request)
                ->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_OPTION_INSERT, $event);

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_OPTION_CREATE_RETURN, $returnEvent);

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
            ->setRequest($request)
            ->setEntity($entity)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new ItemVarOption entity.
     *
     * @Route("/new", name="cart_admin_item_var_option_new")
     * @Method("GET")
     */
    public function newAction(Request $request)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_item_var_option_create', ['datatype' => $this->dataType]))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a ItemVarOption entity.
     *
     * @Route("/{id}", name="cart_admin_item_var_option_show")
     * @Method("GET")
     */
    public function showAction(Request $request, $id)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        return new JsonResponse($entity->getData());
    }

    /**
     * Displays a form to edit an existing ItemVarOption entity.
     *
     * @Route("/{id}/edit", name="cart_admin_item_var_option_edit")
     * @Method("GET")
     */
    public function editAction(Request $request, $id)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_item_var_option_update', ['id' => $entity->getId(), 'datatype' => $this->dataType]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing ItemVarOption entity.
     *
     * @Route("/{id}", name="cart_admin_item_var_option_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ItemVarOption entity.');
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_item_var_option_update', ['id' => $entity->getId(), 'datatype' => $this->dataType]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $formEvent);

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
                ->dispatch(CoreEvents::ITEM_VAR_OPTION_UPDATE, $event);

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_OPTION_UPDATE_RETURN, $returnEvent);

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
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a ItemVarOption entity.
     *
     * @Route("/{id}", name="cart_admin_item_var_option_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $this->initObjectType($request->get('datatype', ''));

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ItemVarOption entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_OPTION_DELETE, $event);

            $request->getSession()->getFlashBag()->add(
                'success',
                'ItemVarOption Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_item_var_option'));
    }

    /**
     * Mass-Delete Categories
     *
     * @Route("/mass_delete", name="cart_admin_item_var_option_mass_delete")
     * @Method("POST")
     */
    public function massDeleteAction(Request $request)
    {
        $this->initObjectType($request->get('datatype', ''));

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
                    ->dispatch(CoreEvents::ITEM_VAR_OPTION_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' ItemVarOptions Successfully Deleted'
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
            ->setAction($this->generateUrl('cart_admin_item_var_option_delete', array('id' => $id, 'datatype' => $this->dataType)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }
}
