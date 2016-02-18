<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\ProductType;

class ProductAdminForm
{
    protected $entityService;

    protected $currencyService;

    protected $formFactory;

    protected $router;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function onProductAdminForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();

        $formType = new ProductType();
        $formType->setCurrency($this->getCurrencyService()->getBaseCurrency());

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
        ]);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'name',
                    'sku',
                    'slug',
                    'price',
                    'is_enabled',
                    'is_taxable',
                    'is_new',
                    'is_discountable',
                    'is_on_sale',
                ],
            ],
            'stock' => [
                'label'  => 'Stock',
                'id'     => 'stock',
                'fields' => [
                    'is_in_stock',
                    'is_qty_managed',
                    'can_backorder',
                    'qty',
                    'stock_type',
                ],
            ],
            'content' => [
                'label' => 'Content',
                'id' => 'content',
                'fields' => [
//                    'layout',
                    'content',
                    'page_title',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'custom_search',
                    'sort_order',
                ],
            ],
        ];

        $customFields = [];
        $varValues = $entity->getVarValues();
        $varSet = $entity->getItemVarSet();
        if ($varValues && $varSet) {

            $vars = $varSet->getItemVars();
            if ($vars) {
                foreach($vars as $var) {

                    switch($var->getFormInput()) {
                        case 'select':
                        case 'multiselect':
                            $name = 'var_' . $var->getId() . '_option';
                            $options = $var->getItemVarOptions();
                            $choices = [];
                            if ($options) {
                                foreach($options as $option) {
                                    $choices[$option->getId()] = $option->getValue();
                                }
                            }

                            $form->add($name, 'choice', [
                                'mapped'    => false,
                                'choices'   => $choices,
                                'required'  => $var->getIsRequired(),
                                'label'     => $var->getName(),
                                'multiple'  => ($var->getFormInput() == 'multiselect'),
                            ]);

                            $customFields[] = $name;

                            break;
                        default:
                            $name = 'var_' . $var->getId();
                            $form->add($name, 'text', [
                                'mapped' => false,
                                'label'  => $var->getName(),
                            ]);

                            $customFields[] = $name;

                            break;
                    }
                }
            }

            if ($entity->getId()) {

                $objectVars = [];
                foreach($varValues as $varValue) {
                    $var = $varValue->getItemVar();
                    $name = 'var_' . $var->getId();
                    $isMultiple = ($var->getFormInput() == 'multiselect');
                    if ($isMultiple) {
                        $name .= '_option';
                    }

                    $value = ($varValue->getItemVarOption())
                        ? $varValue->getItemVarOption()->getId()
                        : $varValue->getValue();

                    if (isset($objectVars[$name])) {
                        if ($isMultiple) {
                            $objectVars[$name]['value'][] = $value;
                        }
                    } else {
                        $value = $isMultiple ? [$value] : $value;
                        $objectVars[$name] = [
                            //'var' => $var,
                            'value' => $value,
                        ];
                    }
                }

                foreach($objectVars as $name => $objectData) {
                    //$var = $objectData['var'];
                    $value = $objectData['value'];
                    $form->get($name)->setData($value);
                }
            }
        }

        if ($customFields) {

            $formSections['custom'] = [
                'label' => 'Custom',
                'id' => 'custom',
                'fields' => $customFields,
            ];

        }

        $returnData['form_sections'] = $formSections;
        $returnData['form'] = $form;

        $event->setForm($form);
        $event->setReturnData($returnData);
    }
}
