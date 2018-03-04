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

        $event->flashMessages();

        if ($event->isJsonResponse()) {
            return new JsonResponse($event->getSuccess());
        }

        return $this->redirect($this->generateUrl('cart_admin_category'));
    }

    /**
     * Mass-Delete Categories
     */
    public function massDeleteAction(Request $request)
    {
        $ids = $request->get('ids', []);
        $counter = 0;

        if ($ids) {
            foreach($ids as $id) {

                $id = (int) $id;
                $entity = $this->get('cart.entity')->find($this->objectType, $id);
                if (!$entity) {
                    continue;
                }

                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::CATEGORY_DELETE, $event);

                if ($event->getSuccess()) {
                    $counter++;
                } else {

                    $event->addSuccessMessage("{$counter} Categories deleted !");
                    $event->addErrorMessage("Category ID: {$id} could not be deleted");

                    if ($event->isJsonResponse()) {

                        return new JsonResponse([
                            'success' => false,
                            'messages' => $event->getMessages(),
                        ]);
                    } else {

                        return $this->redirect($this->generateUrl('cart_admin_category'));
                    }
                }
            }
        }

        $event = new CoreEvent();
        $event->addSuccessMessage("{$counter} Categories deleted !");
        $event->flashMessages();

        if ($event->isJsonResponse()) {
            return new JsonResponse(true);
        }

        return $this->redirect($this->generateUrl('cart_admin_category'));
    }
}
