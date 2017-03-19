<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Intl\Intl;
use MobileCart\CoreBundle\Form\CustomerType;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CustomerAdminForm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerAdminForm
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

    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
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
     * @param Event $event
     */
    public function onCustomerAdminForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        $allCountries = Intl::getRegionBundle()->getCountryNames();
        $allowedCountries = $this->getCartService()->getAllowedCountryIds();

        $countries = [];
        foreach($allowedCountries as $countryId) {
            $countries[$countryId] = $allCountries[$countryId];
        }

        $formType = new CustomerType();
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
                    'is_enabled',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ],
            'billing' => [
                'label' => 'Billing',
                'id' => 'billing',
                'fields' => [
                    'billing_name',
                    'billing_phone',
                    'billing_street',
                    'billing_street2',
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
                    'shipping_street2',
                    'shipping_city',
                    'shipping_region',
                    'shipping_postcode',
                    'shipping_country_id',
                ],
            ],
            'security' => [
                'label' => 'Security',
                'id' => 'security',
                'fields' => [
                    'password',
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

        $returnData['country_regions'] = $this->getCartService()->getCountryRegions();
        $returnData['form_sections'] = $formSections;
        $returnData['form'] = $form;

        $event->setForm($form);
        $event->setReturnData($returnData);
    }
}
