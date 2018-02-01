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
 * Category controller
 */
class CategoryController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CATEGORY;

    /**
     * Lists Category entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new Category entity
     */
    public function createAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance(EntityConstants::CATEGORY))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_category_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $event);

        $invalid = [];
        if ($event->isFormValid()) {

            $slug = $event->getFormData('slug');

            $existing = $this->get('cart.entity')->findOneBy(EntityConstants::CATEGORY, [
                'slug' => $slug
            ]);

            if ($existing) {
                $invalid['slug'] = ['Slug already exists'];
            } else {

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_INSERT, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_CREATE_RETURN, $event);

                return $event->getResponse();
            }
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse($invalid);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new Category entity
     */
    public function newAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_category_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a Category entity
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
     * Displays a form to edit an existing Category entity
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
            ->setFormAction($this->generateUrl('cart_admin_category_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Category entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_category_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_ADMIN_FORM, $event);

        $invalid = [];
        if ($event->isFormValid()) {

            $slug = $event->getFormData('slug');

            $exists = false;
            $existingSlug = $this->get('cart.entity')->findBy(EntityConstants::CATEGORY, [
                'slug' => $slug
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

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_UPDATE, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_UPDATE_RETURN, $event);

                return $event->getResponse();
            }
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse($invalid);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CATEGORY_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a Category entity
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
            ->setAction($this->generateUrl('cart_admin_category_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }
}
