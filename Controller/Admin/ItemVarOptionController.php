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
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ItemVarOptionController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class ItemVarOptionController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::ITEM_VAR_OPTION;

    /**
     * @var string
     */
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
     * Lists ItemVarOption entities
     */
    public function indexAction(Request $request)
    {
        $this->initObjectType($request->get('datatype', ''));

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new ItemVarOption entity
     */
    public function createAction(Request $request)
    {
        $this->initObjectType($request->get('datatype', ''));
        $entity = $this->get('cart.entity')->getVarOptionInstance($this->dataType);
        
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_option_create', ['datatype' => $this->dataType]))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $event);

        $invalid = [];
        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            if (in_array($event->getEntity()->getItemVar()->getFormInput(), ['select', 'multiselect'])) {
                $formData = $request->request->get($form->getName());
                $event->setFormData($formData);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::ITEM_VAR_OPTION_INSERT, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::ITEM_VAR_OPTION_CREATE_RETURN, $event);

                return $event->getResponse();
            } else {
                $event->addErrorMessage('Custom Field must have Form Input value: Select or Multi Select');
                $invalid['item_var'] = ['Invalid'];
            }
        }

        if ($event->getRequestAccept() == CoreEvent::JSON) {

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
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new ItemVarOption entity
     */
    public function newAction(Request $request)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_option_create', ['datatype' => $this->dataType]))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a ItemVarOption entity
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
     * Displays a form to edit an existing ItemVarOption entity
     */
    public function editAction(Request $request, $id)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_option_update', ['id' => $entity->getId(), 'datatype' => $this->dataType]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing ItemVarOption entity
     */
    public function updateAction(Request $request, $id)
    {
        $this->initObjectType($request->get('datatype', ''));

        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ItemVarOption entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_option_update', ['id' => $entity->getId(), 'datatype' => $this->dataType]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_ADMIN_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_OPTION_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_OPTION_UPDATE_RETURN, $event);

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
            ->dispatch(CoreEvents::ITEM_VAR_OPTION_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a ItemVarOption entity
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
                'Option Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_item_var_option'));
    }

    /**
     * Mass-Delete Categories
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
                count($returnData['item_ids']) . ' Options Successfully Deleted'
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
