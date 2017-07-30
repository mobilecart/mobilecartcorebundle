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
 * Category controller.
 *
 * @Route("/admin/category")
 */
class CategoryController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CATEGORY;

    /**
     * Lists Category entities.
     *
     * @Route("/", name="cart_admin_category")
     * @Method("GET")
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
            ->dispatch(CoreEvents::CATEGORY_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new Category entity.
     *
     * @Route("/", name="cart_admin_category_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $varSet = '';
        if ($varSetId = $request->get('var_set_id', '')) {
            $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        } else {
            $varSets = $this->get('cart.entity')->getVarSets($this->objectType);
            if ($varSets) {
                $varSet = $varSets[0];
            }
        }

        $entity = $this->get('cart.entity')->getInstance(EntityConstants::CATEGORY);
        if ($varSet) {
            $entity->setItemVarSet($varSet);
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_category_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $formEvent);

        $invalid = [];
        $messages = [];
        $form = $formEvent->getForm();
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            $existing = $this->get('cart.entity')->findOneBy(EntityConstants::CATEGORY, [
                'slug' => $formData['slug']
            ]);

            if ($existing) {
                $invalid['slug'] = ['Slug already exists'];
            } else {
                // observe event
                //  add category to indexes, etc
                $event = new CoreEvent();
                $event->setEntity($entity)
                    ->setRequest($request)
                    ->setFormData($formData);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_INSERT, $event);

                $returnEvent = new CoreEvent();
                $returnEvent->setMessages($event->getMessages());
                $returnEvent->setRequest($request);
                $returnEvent->setEntity($entity);
                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_CREATE_RETURN, $returnEvent);

                return $returnEvent->getResponse();
            }
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

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
            ->dispatch(CoreEvents::CATEGORY_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new Category entity.
     *
     * @Route("/new", name="cart_admin_category_new")
     * @Method("GET")
     */
    public function newAction(Request $request)
    {
        $varSet = '';
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

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_category_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setVarSet($varSet)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a Category entity.
     *
     * @Route("/{id}", name="cart_admin_category_show")
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
     * Displays a form to edit an existing Category entity.
     *
     * @Route("/{id}/edit", name="cart_admin_category_edit")
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
            ->setAction($this->generateUrl('cart_admin_category_update', ['id' => $entity->getId()]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Category entity.
     *
     * @Route("/{id}", name="cart_admin_category_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_category_update', ['id' => $entity->getId()]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $formEvent);

        $invalid = [];
        $messages = [];
        $form = $formEvent->getForm();
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            $exists = false;
            $existingSlug = $this->get('cart.entity')->findBy(EntityConstants::CATEGORY, [
                'slug' => $formData['slug'],
            ]);

            if ($existingSlug) {
                foreach($existingSlug as $aProduct) {
                    if ($aProduct->getId() != $entity->getId()) {
                        $exists = true;
                        $invalid['slug'] = ['Slug already exists'];
                        break;
                    }
                }
            }

            if (!$exists) {

                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request)
                    ->setFormData($formData);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_UPDATE, $event);

                $returnEvent = new CoreEvent();
                $returnEvent->setMessages($event->getMessages());
                $returnEvent->setRequest($request);
                $returnEvent->setEntity($entity);
                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_UPDATE_RETURN, $returnEvent);

                return $returnEvent->getResponse();
            }
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

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
            ->dispatch(CoreEvents::CATEGORY_EDIT_RETURN, $event);

        return $event->getResponse();
    }
    /**
     * Deletes a Category entity.
     *
     * @Route("/{id}", name="cart_admin_category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Category entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CATEGORY_DELETE, $event);

            // todo : Event Listener

            $request->getSession()->getFlashBag()->add(
                'success',
                'Category Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_category'));
    }

    /**
     * Mass-Delete Categories
     *
     * @Route("/mass_delete", name="cart_admin_category_mass_delete")
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
                    ->dispatch(CoreEvents::CATEGORY_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' Categories Successfully Deleted'
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
            ->setAction($this->generateUrl('cart_admin_category_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }
}
