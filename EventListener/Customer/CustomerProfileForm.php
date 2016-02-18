<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Intl\Intl;
use MobileCart\CoreBundle\Form\CustomerProfileType;

class CustomerProfileForm
{
    protected $entityService;

    protected $currencyService;

    protected $formFactory;

    protected $cartService;

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

    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    public function getCartService()
    {
        return $this->cartService;
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

    public function onCustomerProfileForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();

        $allCountries = Intl::getRegionBundle()->getCountryNames();
        $allowedCountries = $this->getCartService()->getAllowedCountryIds();

        $countries = [];
        foreach($allowedCountries as $countryId) {
            $countries[$countryId] = $allCountries[$countryId];
        }

        $formType = new CustomerProfileType();
        $formType->setCountries($countries);

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
        ]);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'first_name',
                    'last_name',
                    'email',
                    'billing_name',
                    'billing_phone',
                    'billing_street',
                    'billing_city',
                    'billing_region',
                    'billing_postcode',
                    'billing_country_id',
                ],
            ],
            'shipping' => [
                'label' => 'Shipping',
                'id' => 'shipping',
                'fields' => [
                    'is_shipping_same',
                    'shipping_name',
                    'shipping_phone',
                    'shipping_street',
                    'shipping_city',
                    'shipping_region',
                    'shipping_postcode',
                    'shipping_country_id',
                ],
            ],
            'password' => [
                'label' => 'Password',
                'id' => 'password',
                'fields' => [
                    'password',
                ],
            ],
        ];

        $customFields = [];
        $varSet = $entity->getItemVarSet();
        $varValues = $varSet
            ? $entity->getVarValues()
            : [];

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

        $returnData['country_regions'] = $this->getCartService()->getCountryRegions();

        $event->setForm($form)
            ->setReturnData($returnData);
    }
}
