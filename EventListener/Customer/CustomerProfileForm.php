<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\Intl\Intl;
use MobileCart\CoreBundle\Form\CustomerProfileType;

/**
 * Class CustomerProfileForm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerProfileForm
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
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
     * @param CoreEvent $event
     */
    public function onCustomerProfileForm(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();

        $allCountries = Intl::getRegionBundle()->getCountryNames();
        $allowedCountries = $this->getCartService()->getAllowedCountryIds();

        $countries = [];
        foreach($allowedCountries as $countryId) {
            $countries[$countryId] = $allCountries[$countryId];
        }

        $formType = new CustomerProfileType();
        $formType->setCountries($countries);

        if (!is_bool($entity->getIsShippingSame())) {
            $entity->setIsShippingSame((bool) $entity->getIsShippingSame());
        }

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
        ]);

        $formSections = [
            'general' => [
                'label' => 'Billing',
                'id' => 'general',
                'fields' => [
                    'first_name',
                    'last_name',
                    'email',
                    'billing_name',
                    'billing_company',
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
                'label' => 'Shipping Address',
                'id' => 'shipping',
                'fields' => [
                    'is_shipping_same',
                    'shipping_name',
                    'shipping_company',
                    'shipping_phone',
                    'shipping_street',
                    'shipping_street2',
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

                    $name = $var->getCode();
                    switch($var->getFormInput()) {
                        case 'select':
                        case 'multiselect':

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
                    $name = $var->getCode();
                    $isMultiple = ($var->getFormInput() == 'multiselect');

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
