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
 * ConfigSetting controller
 */
class ConfigSettingController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::CONFIG_SETTING;

    /**
     * Lists ConfigSetting entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new ConfigSetting entity
     */
    public function createAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance(EntityConstants::CONFIG_SETTING);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_config_setting_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONFIG_SETTING_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONFIG_SETTING_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new ConfigSetting entity
     */
    public function newAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_config_setting_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a ConfigSetting entity
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
     * Displays a form to edit an existing ConfigSetting entity
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
            ->setFormAction($this->generateUrl('cart_admin_config_setting_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing ConfigSetting entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ConfigSetting entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_config_setting_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_ADMIN_FORM, $event);

        if ($event->isFormValid()) {

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONFIG_SETTING_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CONFIG_SETTING_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse();
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a ConfigSetting entity
     */
    public function deleteAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ConfigSetting entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CONFIG_SETTING_DELETE, $event);

        $event->flashMessages();

        if ($event->isJsonResponse()) {
            return new JsonResponse($event->getSuccess());
        }

        return $this->redirect($this->generateUrl('cart_admin_config_setting'));
    }

    /**
     * Mass-Delete ConfigSettings
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
                    ->dispatch(CoreEvents::CONFIG_SETTING_DELETE, $event);

                if ($event->getSuccess()) {
                    $counter++;
                } else {

                    $event->addSuccessMessage("{$counter} ConfigSettings deleted !");
                    $event->addErrorMessage("ConfigSetting ID: {$id} could not be deleted");

                    if ($event->isJsonResponse()) {

                        return new JsonResponse([
                            'success' => false,
                            'messages' => $event->getMessages(),
                        ]);
                    } else {

                        return $this->redirect($this->generateUrl('cart_admin_config_setting'));
                    }
                }
            }
        }

        $event = new CoreEvent();
        $event->addSuccessMessage("{$counter} ConfigSettings deleted !");
        $event->flashMessages();

        if ($event->isJsonResponse()) {
            return new JsonResponse(true);
        }

        return $this->redirect($this->generateUrl('cart_admin_config_setting'));
    }
}
