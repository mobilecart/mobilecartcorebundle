<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Frontend;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CustomerAddressController extends Controller
{
    protected $objectType = EntityConstants::CUSTOMER_ADDRESS;

    public function indexAction(Request $request)
    {
        $searchParam = $this->container->getParameter('cart.search.frontend');
        $search = $this->container->get($searchParam);

        $event = new CoreEvent();
        $event->setSearch($search)
            ->setRequest($request)
            ->setUser($this->getUser())
            ->setObjectType($this->objectType);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_SEARCH, $event);

        $nav = new CoreEvent();
        $nav->setReturnData($event->getReturnData())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $listEvent = new CoreEvent();
        $listEvent->setRequest($request)
            ->setUser($this->getUser())
            ->setReturnData($nav->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_LIST, $listEvent);

        return $listEvent->getResponse();
    }

    public function newAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_address_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $formEvent);

        $nav = new CoreEvent();
        $nav->setReturnData($formEvent->getReturnData())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($nav->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_NEW_RETURN, $event);

        return $event->getResponse();
    }

    public function createAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_address_create'))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $formEvent);

        $form = $formEvent->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            // observe event
            //  add customer to indexes, etc
            $event = new CoreEvent();
            $event->setEntity($entity)
                ->setCustomer($this->getUser())
                ->setRequest($request)
                ->setFormData($formData)
                ->setSection(CoreEvent::SECTION_FRONTEND);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_INSERT, $event);

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_CREATE_RETURN, $returnEvent);

            return $returnEvent->getResponse();
        }

        if ($request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '') == 'json') {

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
            ->dispatch(CoreEvents::CUSTOMER_NEW_RETURN, $event);

        return $event->getResponse();
    }

    public function editAction(Request $request)
    {
        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $user = $this->getUser();
        $addressId = $request->get('id', 0);

        $entity = $entityService->find($this->objectType, $addressId);
        if (!$entity || $entity->getCustomer()->getId() != $user->getId()) {
            throw $this->createNotFoundException('Unable to find Address');
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_address_update', ['id' => $addressId]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $formEvent);

        $nav = new CoreEvent();
        $nav->setReturnData($formEvent->getReturnData())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($user)
            ->setEntity($entity)
            ->setReturnData($nav->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    public function updateAction(Request $request)
    {
        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $user = $this->getUser();
        $addressId = $request->get('id', 0);

        $entity = $entityService->findOneBy($this->objectType, [
            'id' => $addressId,
            'customer' => $user,
        ]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Address');
        }

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('customer_address_update', ['id' => $addressId]))
            ->setMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_FORM, $formEvent);

        $form = $formEvent->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            // observe event
            // update entity via command bus
            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request)
                ->setFormData($formData)
                ->setSection(CoreEvent::SECTION_FRONTEND);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_UPDATE, $event);

            $returnEvent = new CoreEvent();
            $returnEvent->setMessages($event->getMessages());
            $returnEvent->setRequest($request);
            $returnEvent->setEntity($entity);
            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::CUSTOMER_ADDRESS_UPDATE_RETURN, $returnEvent);

            return $returnEvent->getResponse();
        }

        $nav = new CoreEvent();
        $nav->setReturnData($formEvent->getReturnData())
            ->setCurrentRoute('customer_addresses');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_NAVIGATION, $nav);

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setUser($user)
            ->setEntity($entity)
            ->setReturnData($nav->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    public function deleteAction(Request $request)
    {
        $entityServiceParam = $this->container->getParameter('cart.load.frontend');
        $entityService = $this->container->get($entityServiceParam);

        $user = $this->getUser();
        $addressId = $request->get('id', 0);

        $entity = $entityService->findOneBy($this->objectType, [
            'id' => $addressId,
            'customer' => $user,
        ]);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Address');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::CUSTOMER_ADDRESS_DELETE, $event);

        return $this->redirect($this->generateUrl('customer_addresses', []));
    }
}
