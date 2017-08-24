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
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Content controller
 */
class ContentController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CONTENT;

    /**
     * Lists Content entities
     */
    public function indexAction(Request $request)
    {
        // Load a service; which extends Search\SearchAbstract
        // The service parameter is stored in the service configuration as a parameter ; (slightly meta)
        // This service could use either MySQL or ElasticSearch, etc for retrieving item data
        $searchParam = $this->container->getParameter('cart.load.admin');
        $search = $this->container->get($searchParam)
            ->setObjectType($this->objectType);

        // Observe Event :
        //  perform custom logic, post-processing

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setSearch($search)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new Content entity
     */
    public function createAction(Request $request)
    {
        $varSet = null;
        if ($varSetId = $request->get('var_set_id', '')) {
            $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        } else {
            $varSets = $this->get('cart.entity')->getVarSets($this->objectType);
            if ($varSets) {
                $varSet = $varSets[0];
            }
        }

        $entity = $this->get('cart.entity')->getInstance(EntityConstants::CONTENT);
        if ($varSet) {
            $entity->setItemVarSet($varSet);
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_content_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_ADMIN_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

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
            ->dispatch(CoreEvents::CONTENT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new Content entity
     */
    public function newAction(Request $request)
    {
        $varSet = null;
        if ($varSetId = $request->get('var_set_id', '')) {
            $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        } else {
            $varSets = $this->get('cart.entity')->getVarSets($this->objectType);
            if ($varSets) {
                $varSet = $varSets[0];
            }
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        if ($varSet) {
            $entity->setItemVarSet($varSet);
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_content_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a Content entity
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
     * Displays a form to edit an existing Content entity
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
            ->setFormAction($this->generateUrl('cart_admin_content_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Content entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Content entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_content_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_ADMIN_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

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
            ->dispatch(CoreEvents::CONTENT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a Content entity
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        if ($form->handleRequest($request)->isValid()) {
            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Content entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_DELETE, $event);

            // todo : event

            $request->getSession()->getFlashBag()->add(
                'success',
                'Content Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_content'));
    }

    /**
     * Mass-Delete Contents
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
                    ->dispatch(CoreEvents::CONTENT_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' Contents Successfully Deleted'
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
            ->setAction($this->generateUrl('cart_admin_content_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }
}
