<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Entity\Product;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\ProductType;

/**
 * Class ProductAdminForm
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    protected $formFactory;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfig;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
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

    /**
     * @param $themeConfig
     * @return $this
     */
    public function setThemeConfig($themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeConfig
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * @param Event $event
     */
    public function onProductAdminForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        $formType = new ProductType();
        $formType->setCurrency($this->getCurrencyService()->getBaseCurrency());
        $formType->setCustomTemplates($this->getThemeConfig()->getObjectTypeTemplates(EntityConstants::PRODUCT));

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
                    'is_public',
                    'is_taxable',
                    'is_discountable',
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
                    'qty_unit',
                    'min_qty',
                    //'stock_type',
                ],
            ],
            'content' => [
                'label' => 'Content',
                'id' => 'content',
                'fields' => [
                    'content',
                    'page_title',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                    'custom_search',
                    'sort_order',
                    'custom_template',
                ],
            ],
            'shipping' => [
                'label' => 'Shipping',
                'id' => 'shipping',
                'fields' => [
                    'weight',
                    'weight_unit',
                    'width',
                    'height',
                    'length',
                    'measure_unit',
                    'source_address_key',
                ],
            ],
        ];

        $customFields = [];
        $varSet = $entity->getItemVarSet();
        $vars = $varSet
            ? $varSet->getItemVars()
            : [];

        $varValues = $entity->getVarValues();

        if ($varSet && $vars) {

            foreach($vars as $var) {

                $name = $var->getCode();

                switch($var->getFormInput()) {
                    case 'select':
                    case 'multiselect':
                        $options = $var->getItemVarOptions();
                        $choices = [];
                        if ($options) {
                            foreach($options as $option) {
                                $choices[$option->getValue()] = $option->getValue();
                            }
                        }

                        $form->add($name, 'choice', [
                            'mapped'    => false,
                            'choices'   => $choices,
                            'required'  => $var->getIsRequired(),
                            'label'     => $var->getName(),
                            'multiple'  => ($var->getFormInput() == EntityConstants::INPUT_MULTISELECT
                                            || ($entity->getType() == Product::TYPE_CONFIGURABLE && $var->getFormInput() == EntityConstants::INPUT_SELECT)),
                        ]);

                        $customFields[] = $name;

                        break;
                    case 'checkbox':

                        $form->add($name, 'checkbox', [
                            'mapped' => false,
                            'required' => false,
                            'label' => $var->getName(),
                        ]);

                        $customFields[] = $name;
                        break;
                    default:
                        $form->add($name, 'text', [
                            'mapped' => false,
                            'label'  => $var->getName(),
                        ]);

                        $customFields[] = $name;

                        break;
                }
            }

            if ($entity->getId()) {

                $objectVars = [];
                foreach($varValues as $varValue) {
                    $var = $varValue->getItemVar();
                    $name = $var->getCode();
                    $isMultiple = ($var->getFormInput() == EntityConstants::INPUT_MULTISELECT
                                    || ($entity->getType() == Product::TYPE_CONFIGURABLE && $var->getFormInput() == EntityConstants::INPUT_SELECT));

                    $value = ($varValue->getItemVarOption())
                        ? $varValue->getItemVarOption()->getValue()
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
                            'input' => $var->getFormInput(),
                        ];
                    }
                }

                foreach($objectVars as $name => $objectData) {
                    //$var = $objectData['var'];
                    $value = $objectData['value'];
                    if ($objectData['input'] == 'checkbox') {
                        $value = (bool) $value;
                    }
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
