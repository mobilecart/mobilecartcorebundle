<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\ShippingMethodType;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ShippingMethodAdminForm
{
    protected $entityService;

    protected $currencyService;

    protected $formFactory;

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

    public function onShippingMethodAdminForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();

        $formType = new ShippingMethodType();
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
                    'title',
                    'company',
                    'method',
                    'price',
                    'min_days',
                    'max_days',
                    'is_taxable',
                    'is_discountable',
                    'is_price_dynamic',
                    'pre_conditions',
                ],
            ],
        ];

        $operators = [
            'gt' => 'Greater Than',
            'gte' => 'Greater Than or Equal To',
            'lt' => 'Less Than',
            'lte' => 'Less Than or Equal To',
            'equals' => 'Equal To',
            'in_array' => 'In List',
            'array_intersect' => 'Intersect Lists',
            'contains' => 'Contains',
        ];

        $logicalOperators = [
            'and' => 'If ALL are True',
            'or' => 'If ANY are True',
        ];

        $containerOperators = [
            'product'  => 'Cart Has a Product',
            'shipment' => 'Cart Has a Shipment',
            'customer' => 'Cart Has a Customer',
        ];

        $varSetData = [];
        $varSetData['product'] = ['name' => 'Product'];
        $varSetData['product']['vars'] = [
            'qty' => ['datatype' => 'number', 'name' => 'Quantity', 'object_type' => 'product'],
            'product_id' => ['datatype' => 'number', 'name' => 'ID', 'object_type' => 'product'],
            'sku' => ['datatype' => 'string', 'name' => 'SKU', 'object_type' => 'product'],
            'price' => ['datatype' => 'number', 'name' => 'Price', 'object_type' => 'product'],
            'weight' => ['datatype' => 'number', 'name' => 'Weight', 'object_type' => 'product'],
            'category_ids_csv' => ['datatype' => 'string', 'name' => 'Category ID\'s', 'object_type' => 'product'],
        ];

        $varSets = $this->getEntityService()->findBy(EntityConstants::ITEM_VAR_SET, [
            'object_type' => [EntityConstants::PRODUCT, EntityConstants::CUSTOMER],
        ]);

        // todo : condense vars from var_set's into object_types
        $varData = [];
        if ($varSets) {
            foreach($varSets as $varSet) {

                $objectType = $varSet->getObjectType();
                if (!isset($varData[$objectType])) {
                    $varData[$objectType] = [
                        'vars' => [],
                    ];
                }

                $vars = $varSet->getItemVars();

                if ($vars) {
                    foreach($vars as $var) {

                        if (isset($varSetData[$objectType]['vars'][$var->getCode()])) {
                            continue;
                        }

                        $varSetData[$objectType]['vars'][$var->getCode()] = [
                            'datatype' => $var->getDatatype(),
                            'name'     => $var->getName(),
                            'object_type' => $objectType,
                        ];
                    }
                }
            }
        }

        $varSetData['cart'] = [
            'name' => 'Shopping Cart',
            'vars' => [
                'subtotal' => [
                    'datatype' => 'number',
                    'name' => 'Subtotal',
                    'object_type' => 'cart',
                ],
                'shipping_method' => [
                    'datatype' => 'string',
                    'name' => 'Shipping Method',
                    'object_type' => 'cart',
                ],
            ],
        ];

        $varSetData['shipment'] = [
            'name' => 'Shipments',
            'vars' => [
                'shipping_method' => [
                    'datatype' => 'string',
                    'name' => 'Shipping Method',
                    'object_type' => 'shipment',
                ],
            ],
        ];

        $varSetData['customer'] = [
            'name' => 'Customer',
            'vars' => [
                'country_id' => [
                    'datatype' => 'string',
                    'name' => 'Country',
                    'object_type' => 'customer',
                ],
                'state' => [
                    'datatype' => 'string',
                    'name' => 'State',
                    'object_type' => 'customer',
                ],
                'zipcode' => [
                    'datatype' => 'string',
                    'name' => 'Postal',
                    'object_type' => 'customer',
                ],
            ],
        ];

        $conditionInput = '#shipping_method_pre_conditions';
        $container = 'div#section-general';

        $returnData = array_merge($returnData, [
            'entity' => $entity,
            'form' => $form,
            'form_sections' => $formSections,
            'operators_json' => json_encode($operators),
            'logical_operators_json' => json_encode($logicalOperators),
            'container_operators_json' => json_encode($containerOperators),
            'condition_input' => $conditionInput,
            //'target_input' => $targetInput,
            'container' => $container,
            'var_sets' => json_encode($varSetData),
        ]);

        $event->setForm($form)
            ->setReturnData($returnData);
    }
}
