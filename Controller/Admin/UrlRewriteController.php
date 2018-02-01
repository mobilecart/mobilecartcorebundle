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
 * Class UrlRewriteController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class UrlRewriteController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::URL_REWRITE;

    /**
     * Lists UrlRewrite entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new UrlRewrite entity
     */
    public function createAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance(EntityConstants::URL_REWRITE))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_url_rewrite_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::URL_REWRITE_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::URL_REWRITE_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new UrlRewrite entity
     */
    public function newAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($this->get('cart.entity')->getInstance($this->objectType))
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_url_rewrite_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a UrlRewrite entity
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
     * Displays a form to edit an existing UrlRewrite entity
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
            ->setFormAction($this->generateUrl('cart_admin_url_rewrite_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing UrlRewrite entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Url Rewrite entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_url_rewrite_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::URL_REWRITE_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::URL_REWRITE_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::URL_REWRITE_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a UrlRewrite entity
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find UrlRewrite entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::URL_REWRITE_DELETE, $event);

            $request->getSession()->getFlashBag()->add(
                'success',
                'UrlRewrite Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_url_rewrite'));
    }

    /**
     * Mass-Delete Url Rewrite entities
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
                    ->dispatch(CoreEvents::URL_REWRITE_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' UrlRewrites Successfully Deleted'
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
            ->setAction($this->generateUrl('cart_admin_url_rewrite_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }
}
