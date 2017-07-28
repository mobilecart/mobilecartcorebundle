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

use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Product controller.
 *
 * @Route("/admin/product")
 */
class ProductController extends Controller
{

    protected $objectType = EntityConstants::PRODUCT;

    /**
     * Lists Product entities
     *
     * @Route("/", name="cart_admin_product")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        // Load a service; which extends Search\SearchAbstract
        // The service parameter is stored in the service configuration as a parameter ; (slightly meta)
        // This service could use either MySQL or ElasticSearch, etc for retrieving item data
        $searchParam = $this->container->getParameter('cart.load.admin');
        $search = $this->container->get($searchParam);

        // Observe Event :
        //  perform custom logic, post-processing

        $event = new CoreEvent();
        $event->setRequest($request)
            ->setSearch($search)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $event);

        $search = $event->getSearch();

        if (in_array($search->getFormat(), ['', 'html'])) {
            // for storing the last grid filters in the url ; used in back links
            $request->getSession()->set('cart_admin_product', $request->getQueryString());
        }

        // Data for Template, etc
        $returnData = array_merge(
            $event->getReturnData(), [
            'search' => $search,
            'result' => $search->getResult(),
        ]);

        // Observe Event :
        //  populate grid columns and mass actions,
        //  continue building return data

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setRequest($request)
            ->setReturnData($returnData)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_LIST, $event);

        return $event->getResponse();
    }

    /**
     * Creates a form to init a product create form; with correct fields
     *  ie what type of product, and which var set ?
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createInitForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart_admin_product_new'))
            ->setMethod('GET')
            ->getForm();
    }

    /**
     * Displays a form to gather required fields for
     *  properly building the create form in the newAction
     *
     * @Route("/init", name="cart_admin_product_init")
     * @Method("GET")
     */
    public function initAction(Request $request)
    {
        $form = $this->createInitForm();

        $form->add('type', 'choice', [
            'mapped'    => false,
            'choices'   => $this->get('cart.entity')->getProductTypes(),
            'required'  => 1,
            'label'     => 'Product Type',
            'multiple'  => 0,
        ]);

        $varSetChoices = [];
        $varSets = $this->get('cart.entity')
            ->getVarSets($this->objectType);

        if ($varSets) {
            foreach($varSets as $varSet) {
                $varSetChoices[$varSet->getId()] = $varSet->getName();
            }
        }

        $form->add('var_set_id', 'choice', [
            'mapped'    => false,
            'choices'   => $varSetChoices,
            'required'  => 1,
            'label'     => 'Field Set',
            'multiple'  => 0,
        ]);

        $returnData = [
            'init_form' => $form->createView(),
        ];

        $tplPath = $this->get('cart.theme')->getTemplatePath('admin');
        $view = $tplPath . 'Product:init.html.twig';
        return $this->render($view, $returnData);
    }

    /**
     * Displays a form to create a new Product entity.
     *
     * @Route("/new", name="cart_admin_product_new")
     * @Method("GET")
     */
    public function newAction(Request $request)
    {
        // we must first know what we are creating
        // eg which product->var_set and product->type

        if (!$formData = $request->get('form')) {
            return $this->redirect($this->generateUrl('cart_admin_product_init'));
        }

        $varSetId = isset($formData['var_set_id']) ? $formData['var_set_id'] : '';
        $type = isset($formData['type']) ? $formData['type'] : '';
        $types = $this->get('cart.entity')->getProductTypes();

        if (!$varSetId || !$type || !isset($types[$type])) {
            return $this->redirect($this->generateUrl('cart_admin_product_init'));
        }
        $typeLabel = $types[$type];

        $varSet = $this->get('cart.entity')
            ->getVarSet($varSetId);

        if (!$varSet) {
            return $this->redirect($this->generateUrl('cart_admin_product_init'));
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $entity->setType($type);
        $entity->setItemVarSet($varSet);

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_product_create', [
                'var_set_id' => $varSetId,
                'type'       => $type
            ]))
            ->setReturnData([
                'type' => $typeLabel,
            ])
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setVarSet($varSet)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a Product entity.
     *
     * @Route("/{id}", name="cart_admin_product_show")
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
     * Creates a new Product entity.
     *
     * @Route("/", name="cart_admin_product_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $varSetId = $request->get('var_set_id', '');
        $type = $request->get('type', '');
        $types = $this->get('cart.entity')->getProductTypes();

        if (!$varSetId || !$type || !isset($types[$type])) {
            return $this->redirect($this->generateUrl('cart_admin_product_new'));
        }
        $typeLabel = $types[$type];

        $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        if (!$varSet) {
            return $this->redirect($this->generateUrl('cart_admin_product_new'));
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $entity->setType($type);
        $entity->setItemVarSet($varSet);

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_product_create', [
                'var_set_id' => $varSetId,
                'type'       => $type
            ]))
            ->setMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $formEvent);

        $invalid = [];
        $messages = [];
        $form = $formEvent->getForm();
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            $existing = $this->get('cart.entity')->findOneBy(EntityConstants::PRODUCT, [
                'slug' => $formData['slug']
            ]);

            if ($existing) {
                $invalid['slug'] = ['Slug already exists'];
            } else {

                // observe event
                //  add product to indexes, etc
                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request)
                    ->setFormData($formData);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::PRODUCT_INSERT, $event);

                $returnEvent = new CoreEvent();
                $returnEvent->setMessages($event->getMessages());
                $returnEvent->setRequest($request);
                $returnEvent->setEntity($entity);
                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::PRODUCT_CREATE_RETURN, $returnEvent);

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
            ->dispatch(CoreEvents::PRODUCT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit", name="cart_admin_product_edit")
     * @Method("GET")
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $types = $this->get('cart.entity')->getProductTypes();
        $type = $types[$entity->getType()];

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_product_update', ['id' => $entity->getId()]))
            ->setMethod('PUT')
            ->setReturnData(['type' => $type]);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $formEvent);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setReturnData($formEvent->getReturnData());

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Product entity.
     *
     * @Route("/{id}", name="cart_admin_product_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $types = $this->get('cart.entity')->getProductTypes();
        $type = $types[$entity->getType()];

        $formEvent = new CoreEvent();
        $formEvent->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setAction($this->generateUrl('cart_admin_product_update', ['id' => $entity->getId()]))
            ->setMethod('PUT')
            ->setReturnData(['type' => $type]);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $formEvent);

        $invalid = [];
        $messages = [];
        $form = $formEvent->getForm();
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());

            $exists = false;
            $existingSlug = $this->get('cart.entity')->findBy(EntityConstants::PRODUCT, [
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

            $existingSku = $this->get('cart.entity')->findBy(EntityConstants::PRODUCT, [
                'sku' => $formData['sku'],
            ]);

            if ($existingSku) {
                foreach($existingSku as $aProduct) {
                    if ($aProduct->getId() != $entity->getId()) {
                        $exists = true;
                        $invalid['sku'] = ['SKU already exists'];
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
                    ->dispatch(CoreEvents::PRODUCT_UPDATE, $event);

                $returnEvent = new CoreEvent();
                $returnEvent->setMessages($event->getMessages());
                $returnEvent->setRequest($request);
                $returnEvent->setEntity($entity);
                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::PRODUCT_UPDATE_RETURN, $returnEvent);

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
            ->dispatch(CoreEvents::PRODUCT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a Product entity.
     *
     * @Route("/{id}", name="cart_admin_product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Product entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::PRODUCT_DELETE, $event);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Product Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_product'));
    }

    /**
     * Duplicates a Product entity.
     *
     * @Route("/{id}", name="cart_admin_product_duplicate")
     * @Method("POST")
     */
    public function duplicateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_DUPLICATE, $event);

        $request->getSession()->getFlashBag()->add(
            'success',
            'Product Successfully Duplicated!'
        );

        $returnEvent = new CoreEvent();
        $returnEvent->setMessages($event->getMessages());
        $returnEvent->setRequest($request);
        $returnEvent->setEntity($event->getEntity());
        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_UPDATE_RETURN, $returnEvent);

        return $returnEvent->getResponse();
    }

    /**
     * Mass-Update Products
     *
     * @Route("/mass_update", name="cart_admin_product_mass_update")
     * @Method("POST")
     */
    public function massUpdateAction(Request $request)
    {
        $itemIds = $request->get('item_ids', []);
        $varCode = $request->get('var_code', '');
        $value = $request->get('value','');
        $returnData = ['item_ids' => []];

        if ($itemIds) {

            $repo = $this->get('cart.entity')->getRepository($this->objectType);

            $sortable = $repo->getSortableFields();

            foreach($itemIds as $itemId) {

                $entity = $this->get('cart.entity')
                    ->find($this->objectType, $itemId);

                if (!$entity) {
                    continue;
                }

                if (isset($sortable[$varCode])) {
                    $entity->set($varCode, $value);

                    // observe event
                    // update entity via command bus
                    $event = new CoreEvent();
                    $event->setObjectType($this->objectType)
                        ->setEntity($entity)
                        ->setRequest($request)
                        ->setFormData([]);

                    $this->get('event_dispatcher')
                        ->dispatch(CoreEvents::PRODUCT_UPDATE, $event);

                    $returnData['item_ids'][] = $entity->getId();
                }
            }
        }

        $request->getSession()->getFlashBag()->add(
            'success',
            count($returnData['item_ids']) . ' Product(s) Successfully Updated'
        );

        return new JsonResponse($returnData);
    }

    /**
     * Mass-Delete Products
     *
     * @Route("/mass_delete", name="cart_admin_product_mass_delete")
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
                    ->dispatch(CoreEvents::PRODUCT_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' Product(s) Successfully Deleted'
            );
        }

        return new JsonResponse($returnData);
    }

    /**
     * Creates a form to delete a Item entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart_admin_product_delete', ['id' => $id]))
            ->setMethod('DELETE')
            ->add('submit', 'submit', [
                'label' => 'Delete',
                'attr' => [
                    'class' => 'btn btn-danger',
                ]
            ])
            ->getForm();
    }
}
