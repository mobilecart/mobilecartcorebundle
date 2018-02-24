<?php

namespace MobileCart\CoreBundle\EventListener\Discount;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\CartComponent\Discount;

/**
 * Class DiscountAdminForm
 * @package MobileCart\CoreBundle\EventListener\Discount
 */
class DiscountAdminForm
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $formTypeClass
     * @return $this
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onDiscountAdminForm(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]);

        $operators = [
            'gt' => ['label' => 'Greater Than', 'types' => ['number', 'date']],
            'gte' => ['label' => 'Greater Than or Equal To', 'types' => ['number', 'date']],
            'lt' => ['label' => 'Less Than', 'types' => ['number', 'date']],
            'lte' => ['label' => 'Less Than or Equal To', 'types' => ['number', 'date']],
            'equals' => ['label' => 'Equal To', 'types' => ['number', 'string', 'date', 'boolean']],
            'in_array' => ['label' => 'In List', 'types' => ['number', 'string']],
            //'array_intersect' => ['label' => 'Intersect Lists', 'types' => ['number', 'string']],
            'starts' => ['label' => 'Starts With', 'types' => ['string']],
            'ends' => ['label' => 'Ends With', 'types' => ['string']],
            'contains' => ['label' => 'Contains', 'types' => ['string']],
        ];

        $logicalOperators = [
            Discount::OPERATOR_AND => 'If ALL are True',
            Discount::OPERATOR_OR => 'If ANY are True',
        ];

        $targetLogicalOperators = [
            Discount::OPERATOR_AND => 'If ALL are True',
            Discount::OPERATOR_OR => 'If ANY are True',
            Discount::TARGET_ALL_ITEMS => 'All Discountable Items',
            Discount::TARGET_ALL_SHIPMENTS => 'All Discountable Shipments',
        ];

        $containerOperators = [
            Discount::PRODUCT  => 'Cart Has a Product', // currently, this needs to line up with entity-type shortcodes also eg product, shipment, customer
            Discount::SHIPMENT => 'Cart Has a Shipment', // so , don't change these without creating a mapper function of some sort
            Discount::CUSTOMER => 'Cart Has a Customer',
            Discount::CART     => 'Cart Has',
        ];

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'is_auto',
                    'name',
                    'priority',
                    'is_stopper',
                    'applied_as',
                    'value',
                    'coupon_code',
                    'promo_skus',
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
                    'max_amount',
                    'is_pre_tax',
                    'is_compound',
                    'is_max_per_item',
                    'is_proportional',
                    'max_qty',
                ],
            ],
        ];

        $container = 'div#section-general';
        $conditionInput = '#discount_pre_conditions';
        $targetInput = '#discount_target_conditions';

        $varSetData = [];

        $varSetData['cart'] = [
            'name' => 'Shopping Cart',
            'vars' => [
                'base_item_total' => [
                    'datatype' => 'number',
                    'name' => 'Item Sub-Total',
                    'object_type' => 'cart',
                ],
                'base_total' => [
                    'datatype' => 'number',
                    'name' => 'Grand Total',
                    'object_type' => 'cart',
                ],
                'base_shipment_total' => [
                    'datatype' => 'number',
                    'name' => 'Shipping Total',
                    'object_type' => 'cart',
                ],
                'discounted_item_total' => [
                    'datatype' => 'number',
                    'name' => 'Discounted Item Total',
                    'object_type' => 'cart',
                ],
                'discounted_shipment_total' => [
                    'datatype' => 'number',
                    'name' => 'Discounted Shipping Total',
                    'object_type' => 'cart',
                ],
            ],
        ];

        $varSetData['product'] = [
            'name' => 'Product',
            'vars' => [
                'qty' => ['datatype' => 'number', 'name' => 'Quantity', 'object_type' => 'product'],
                'product_id' => ['datatype' => 'number', 'name' => 'ID', 'object_type' => 'product'],
                'sku' => ['datatype' => 'string', 'name' => 'SKU', 'object_type' => 'product'],
                'price' => ['datatype' => 'number', 'name' => 'Price', 'object_type' => 'product'],
                'weight' => ['datatype' => 'number', 'name' => 'Weight', 'object_type' => 'product'],
                'category_ids_csv' => ['datatype' => 'string', 'name' => 'Category ID\'s', 'object_type' => 'product'],
            ],
        ];

        $varSets = $this->getEntityService()->findBy(EntityConstants::ITEM_VAR_SET, [
            'object_type' => [
                EntityConstants::PRODUCT,
                EntityConstants::CUSTOMER
            ],
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

                        $datatype = 'string';
                        if (in_array($var->getDatatype(), ['int', 'decimal'])) {
                            $datatype = 'number';
                        }

                        $configData = [
                            'datatype' => $datatype,
                            'name'     => $var->getName(),
                            'object_type' => $objectType,
                        ];

                        if (in_array($var->getFormInput(), ['select', 'multiselect'])) {
                            $options = $var->getItemVarOptions();
                            if ($options) {
                                $selectOptions = [];
                                foreach($options as $option) {
                                    $selectOptions[] = $option->getValue();
                                }
                                $configData['options'] = $selectOptions;
                            }
                        }

                        $varSetData[$objectType]['vars'][$var->getCode()] = $configData;
                    }
                }
            }
        }

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
                'email' => [
                    'datatype' => 'string',
                    'name' => 'Email',
                    'object_type' => 'customer',
                ],
                'billing_region' => [
                    'datatype' => 'string',
                    'name' => 'Billing State',
                    'object_type' => 'customer',
                ],
                'billing_country_id' => [
                    'datatype' => 'string',
                    'name' => 'Billing Country',
                    'object_type' => 'customer',
                ],
                'billing_postcode' => [
                    'datatype' => 'string',
                    'name' => 'Billing Postcode',
                    'object_type' => 'customer',
                ],
                'shipping_region' => [
                    'datatype' => 'string',
                    'name' => 'Shipping State',
                    'object_type' => 'customer',
                ],
                'shipping_country_id' => [
                    'datatype' => 'string',
                    'name' => 'Shipping Country',
                    'object_type' => 'customer',
                ],
                'shipping_postcode' => [
                    'datatype' => 'string',
                    'name' => 'Shipping Postcode',
                    'object_type' => 'customer',
                ],
            ],
        ];

        $event->addReturnData([
            'entity' => $entity,
            //'form' => $form,
            'form_sections' => $formSections,
            'operators_json' => json_encode($operators),
            'logical_operators_json' => json_encode($logicalOperators),
            'target_logical_operators_json' => json_encode($targetLogicalOperators),
            'container_operators_json' => json_encode($containerOperators),
            'condition_input' => $conditionInput,
            'target_input' => $targetInput,
            'container' => $container,
            'var_sets' => json_encode($varSetData),
        ]);

        $event->setForm($form);
    }
}
