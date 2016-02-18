<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use Symfony\Component\EventDispatcher\Event;

class ShippingMethodNewReturn
{
    protected $request;

    protected $varSet;

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

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setVarSet($varSet)
    {
        $this->varSet = $varSet;
        return $this;
    }

    public function getVarSet()
    {
        return $this->varSet;
    }

    public function onShippingMethodNewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $entity = $event->getEntity();
        $typeSections = [];
        $returnData['template_sections'] = $typeSections;

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
            'and' => 'If All are True:',
            'or' => 'If Any are True:',
        ];

        $returnData['operators_json'] = json_encode($operators);
        $returnData['logical_operators_json'] = json_encode($logicalOperators);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'title', 'company', 'method', 'price', 'min_days', 'max_days',
                    'is_taxable', 'is_discountable', 'is_price_dynamic', 'pre_conditions',
                ],
            ],
        ];

        $returnData['form_sections'] = $formSections;

        $returnData['condition_input'] = '#mobilecart_corebundle_shippingmethod_pre_conditions';
        $returnData['container'] = 'div#section-general';

        $productVars = [
            'qty' => ['datatype' => 'number', 'name' => 'Quantity'],
            'id' => ['datatype' => 'number', 'name' => 'ID'],
            'sku' => ['datatype' => 'string', 'name' => 'SKU'],
            'price' => ['datatype' => 'number', 'name' => 'Price'],
            'weight' => ['datatype' => 'number', 'name' => 'Weight'],
            'category_ids_csv' => ['datatype' => 'string', 'name' => 'Category ID\'s'],
        ];

        $varSetData = [];
        $varSets = $this->getEntityService()->getRepository('item_var_set')
            ->findAll();

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

                // todo : ensure this is handled
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
                ]
            ]
        ];


        $returnData['var_sets'] = json_encode($varSetData);

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();
        $returnData['entity'] = $entity;

        $event->setReturnData($returnData);
    }
}
