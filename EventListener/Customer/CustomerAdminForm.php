<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CustomerAdminForm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerAdminForm(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Customer $entity */
        $entity = $event->getEntity();

        // find variant set
        if (!$entity->getId() && !$entity->getItemVarSet()) {
            $varSet = $this->getEntityService()->findOneBy(EntityConstants::ITEM_VAR_SET, [
                'object_type' => EntityConstants::CUSTOMER
            ]);
            if ($varSet) {
                $entity->setItemVarSet($varSet);
            }
        }

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'email',
                    'billing_firstname',
                    'billing_lastname',
                    'password',
                    'is_enabled',
                ],
            ],
            'billing' => [
                'label' => 'Billing Address',
                'id' => 'billing',
                'fields' => [
                    'is_shipping_same',
                    'billing_company',
                    'billing_street',
                    'billing_street2',
                    'billing_city',
                    'billing_region',
                    'billing_postcode',
                    'billing_country_id',
                    'billing_phone',
                ],
            ],
            'shipping' => [
                'label' => 'Shipping Address',
                'id' => 'shipping',
                'fields' => [
                    'shipping_firstname',
                    'shipping_lastname',
                    'shipping_company',
                    'shipping_street',
                    'shipping_street2',
                    'shipping_city',
                    'shipping_region',
                    'shipping_postcode',
                    'shipping_country_id',
                    'shipping_phone',
                ],
            ],
            'security' => [
                'label' => 'Security',
                'id' => 'security',
                'fields' => [
                    'is_locked',
                    'is_password_expired',
                    'is_expired',
                    'api_key',
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
                            'multiple'  => ($var->getFormInput() == 'multiselect'),
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
                    $isMultiple = ($var->getFormInput() == EntityConstants::INPUT_MULTISELECT);

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

        $event->setReturnData('country_regions', $this->getCartService()->getCountryRegions());
        $event->setReturnData('form_sections', $formSections);
        $event->setForm($form);
    }
}
