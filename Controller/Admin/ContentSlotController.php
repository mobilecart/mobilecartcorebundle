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
     * Deletes a Content entity
     */
    public function deleteAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {

            return new JsonResponse([
                'success' => false,
            ]);
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONTENT_SLOT_DELETE, $event);

        if ($event->getRequestAccept() == CoreEvent::JSON) {

            return new JsonResponse([
                'success' => true,
            ]);
        }

        $request->getSession()->getFlashBag()->add(
            'success',
            'Content Successfully Deleted!'
        );

        return $this->redirect($this->generateUrl('cart_admin_content_slot'));
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
                    ->dispatch(CoreEvents::CONTENT_SLOT_DELETE, $event);

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
            ->setAction($this->generateUrl('cart_admin_content_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete', 'required' => false])
            ->getForm();
    }
}
