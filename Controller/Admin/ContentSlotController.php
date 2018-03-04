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
 * Class ContentSlotController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class ContentSlotController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CONTENT_SLOT;

    /**
     * Lists Content entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new Content entity
     */
    public function createAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance(EntityConstants::CONTENT_SLOT))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_content_slot_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_SLOT_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_SLOT_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new Content entity
     */
    public function newAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_content_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_NEW_RETURN, $event);

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
            ->dispatch(CoreEvents::CONTENT_SLOT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Content entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentSlot entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_content_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_SLOT_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONTENT_SLOT_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a ContentSlot entity
     */
    public function deleteAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ContentSlot entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_DELETE, $event);

        $event->flashMessages();

        if ($event->isJsonResponse()) {
            return new JsonResponse($event->getSuccess());
        }

        return $this->redirect($this->generateUrl('cart_admin_content_slot'));
    }

    /**
     * Mass-Delete ContentSlots
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
                    ->dispatch(CoreEvents::CONTENT_SLOT_DELETE, $event);

                if ($event->getSuccess()) {
                    $counter++;
                } else {

                    $event->addSuccessMessage("{$counter} ContentSlots deleted !");
                    $event->addErrorMessage("ContentSlot ID: {$id} could not be deleted");

                    if ($event->isJsonResponse()) {

                        return new JsonResponse([
                            'success' => false,
                            'messages' => $event->getMessages(),
                        ]);
                    } else {

                        return $this->redirect($this->generateUrl('cart_admin_content_slot'));
                    }
                }
            }
        }

        $event = new CoreEvent();
        $event->addSuccessMessage("{$counter} ContentSlots deleted !");
        $event->flashMessages();

        if ($event->isJsonResponse()) {
            return new JsonResponse(true);
        }

        return $this->redirect($this->generateUrl('cart_admin_content_slot'));
    }
}
