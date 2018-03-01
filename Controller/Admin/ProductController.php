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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ProductController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class ProductController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::PRODUCT;

    /**
     * Lists Product entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_SEARCH, $event);

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
     */
    public function initAction(Request $request)
    {
        $form = $this->createInitForm();

        $form->add('type', ChoiceType::class, [
            'mapped'    => false,
            'choices'   => array_flip($this->get('cart.entity')->getProductTypes()),
            'required'  => 1,
            'label'     => 'Product Type',
            'multiple'  => 0,
            'choices_as_values' => true,
        ]);

        $varSetChoices = [];
        $varSets = $this->get('cart.entity')
            ->getVarSets($this->objectType);

        if ($varSets) {
            foreach($varSets as $varSet) {
                $varSetChoices[$varSet->getId()] = $varSet->getName();
            }
        }

        $form->add('var_set_id', ChoiceType::class, [
            'mapped'    => false,
            'choices'   => array_flip($varSetChoices),
            'required'  => 1,
            'label'     => 'Field Set',
            'multiple'  => 0,
            'choices_as_values' => true,
        ]);

        $returnData = [
            'init_form' => $form->createView(),
        ];

        $tplPath = $this->get('cart.theme')->getTemplatePath('admin');
        $view = $tplPath . 'Product:init.html.twig';
        return $this->render($view, $returnData);
    }

    /**
     * Displays a form to create a new Product entity
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

        $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        if (!$varSet) {
            return $this->redirect($this->generateUrl('cart_admin_product_init'));
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $entity->setType($type);
        $entity->setItemVarSet($varSet);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_product_create', [
                'var_set_id' => $varSetId,
                'type'       => $type
            ]))
            ->setReturnData([
                'type' => $types[$type],
            ])
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a Product entity
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
     * Creates a new Product entity
     */
    public function createAction(Request $request)
    {
        $varSetId = $request->get('var_set_id', '');
        $type = $request->get('type', '');
        $types = $this->get('cart.entity')->getProductTypes();

        if (!$varSetId || !$type || !isset($types[$type])) {
            return $this->redirect($this->generateUrl('cart_admin_product_new'));
        }

        $varSet = $this->get('cart.entity')->getVarSet($varSetId);
        if (!$varSet) {
            return $this->redirect($this->generateUrl('cart_admin_product_new'));
        }

        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $entity->setType($type);
        $entity->setItemVarSet($varSet);

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_product_create', [
                'var_set_id' => $varSetId,
                'type'       => $type
            ]))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $event);

        $invalid = [];
        if ($event->isFormValid()) {

            $slug = $event->getFormData('slug');
            $existing = $this->get('cart.entity')->findOneBy(EntityConstants::PRODUCT, [
                'slug' => $slug
            ]);

            if ($existing) {
                $invalid['slug'] = ['Slug already exists'];
            } else {

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::PRODUCT_INSERT, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::PRODUCT_CREATE_RETURN, $event);

                return $event->getResponse();
            }
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse($invalid);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to edit an existing Product entity
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $types = $this->get('cart.entity')->getProductTypes();
        $type = $types[$entity->getType()];

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_product_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT')
            ->setReturnData('type', $type);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing Product entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Product entity.');
        }

        $types = $this->get('cart.entity')->getProductTypes();
        $type = $types[$entity->getType()];

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_product_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT')
            ->setReturnData(['type' => $type]);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_ADMIN_FORM, $event);

        $invalid = [];
        if ($event->isFormValid()) {

            $slug = $event->getFormData('slug');
            $sku = $event->getFormData('sku');

            $exists = false;
            $existingSlug = $this->get('cart.entity')->findBy(EntityConstants::PRODUCT, [
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

            $existingSku = $this->get('cart.entity')->findBy(EntityConstants::PRODUCT, [
                'sku' => $sku
            ]);

            // this logic only matters if you don't have a Unique constraint on the table column in the db
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

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::PRODUCT_UPDATE, $event);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::PRODUCT_UPDATE_RETURN, $event);

                return $event->getResponse();
            }
        }

        if ($event->isJsonResponse()) {
            return $event->getInvalidFormJsonResponse($invalid);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a Product entity
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
     * Duplicates a Product entity
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

        $event->addSuccessMessage('Product Successfully Duplicated!');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::PRODUCT_UPDATE_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Mass-Update Products
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
                        ->setIsMassUpdate(true)
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
                    ->setRequest($request)
                    ->setIsMassUpdate(true);

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
