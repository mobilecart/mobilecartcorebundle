<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\DiscountType;
use MobileCart\CoreBundle\Constants\EntityConstants;

class DiscountAdminForm
{

    protected $formFactory;

    protected $entityService;

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

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
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

    public function onDiscountAdminForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();

        $form = $this->getFormFactory()->create(new DiscountType(), $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
        ]);

        $operators = [
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'equals' => '==',
            'in_array' => 'In:',
            'array_intersect' => 'Intersect:',
            'contains' => 'Contains:',
        ];

        $logicalOperators = [
            'and' => 'If ALL are True',
            'or' => 'If ANY are True',
        ];

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'is_auto',
                    'name',
                    'applied_as',
                    'value',
                    'coupon_code',
                    'applied_to',
                    'pre_conditions',
                    'target_conditions',
                ],
            ],
            'advanced' => [
                'label' => 'Advanced',
                'id' => 'advanced',
                'fields' => [
                    'start_time',
                    'end_time',
                    'priority',
                    'is_stopper',
                    'is_pre_tax',
                    'is_compound',
                    'is_max_per_item',
                    'is_proportional',
                    'max_amount',
                    'max_qty',
                ],
            ],
        ];

        $container = 'div#section-general';
        $conditionInput = '#discount_pre_conditions';
        $targetInput = '#discount_target_conditions';

        $varSetData = [];

        $productVars = [
            'qty' => ['datatype' => 'number', 'name' => 'Quantity'],
            'id' => ['datatype' => 'number', 'name' => 'ID'],
            'sku' => ['datatype' => 'string', 'name' => 'SKU'],
            'price' => ['datatype' => 'number', 'name' => 'Price'],
            'weight' => ['datatype' => 'number', 'name' => 'Weight'],
            'category_ids_csv' => ['datatype' => 'string', 'name' => 'Category ID\'s'],
        ];

        $varSets = $this->getEntityService()->findAll(EntityConstants::ITEM_VAR_SET);

        if ($varSets) {
            foreach($varSets as $varSet) {

                $varData = $productVars;
                $vars = $varSet->getItemVars();

                if ($vars) {
                    foreach($vars as $var) {
                        $varData[$var->getCode()] = [
                            'datatype' => $var->getDatatype(),
                            'name'     => $var->getName()
                        ];
                    }
                }

                $varSetData['set_' . $varSet->getId()] = [
                    'name' => $varSet->getName(),
                    'vars' => $varData,
                ];
            }
        }

        $varSetData['_shopping_cart'] = [
            'name' => 'Shopping Cart',
            'vars' => [
                'subtotal' => [
                    'datatype' => 'number',
                    'name' => 'Subtotal',
                ],
                'shipping_method' => [
                    'datatype' => 'string',
                    'name' => 'Shipping Method',
                ],
            ],
        ];

        $varSetData['_customer'] = [
            'name' => 'Customer',
            'vars' => [
                'state' => [
                    'datatype' => 'string',
                    'name' => 'State',
                ],
                'zipcode' => [
                    'datatype' => 'string',
                    'name' => 'Postal',
                ],
            ],
        ];

        $returnData = array_merge($returnData, [
            'entity' => $entity,
            'form' => $form,
            'form_sections' => $formSections,
            'operators_json' => json_encode($operators),
            'logical_operators_json' => json_encode($logicalOperators),
            'condition_input' => $conditionInput,
            'target_input' => $targetInput,
            'container' => $container,
            'var_sets' => json_encode($varSetData),
        ]);

        $returnData['form'] = $form;

        $event->setForm($form)
            ->setReturnData($returnData);
    }
}
