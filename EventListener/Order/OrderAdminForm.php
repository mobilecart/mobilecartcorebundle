<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class OrderAdminForm
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderAdminForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCurrencyService();
    }

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
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
     * @param CoreEvent $event
     */
    public function onOrderAdminForm(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Order $entity */
        $entity = $event->getEntity();
        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $entity, [
            'action' => $event->getFormAction(),
            'method' => $event->getFormMethod(),
        ]);

        $formSections = [];
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

                        $form->add($name, ChoiceType::class, [
                            'mapped'    => false,
                            'choices'   => $choices,
                            'required'  => $var->getIsRequired(),
                            'label'     => $var->getName(),
                            'multiple'  => ($var->getFormInput() == 'multiselect'),
                        ]);

                        $customFields[] = $name;
                        break;
                    case 'checkbox':

                        $form->add($name, CheckboxType::class, [
                            'mapped' => false,
                            'required' => false,
                            'label' => $var->getName(),
                        ]);

                        $customFields[] = $name;
                        break;
                    default:

                        $form->add($name, TextType::class, [
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

        $event->setReturnData('form_sections', $formSections);
        $event->setReturnData('country_regions', $this->getCartService()->getCountryRegions());
        $event->setReturnData('form', $form);
    }
}
