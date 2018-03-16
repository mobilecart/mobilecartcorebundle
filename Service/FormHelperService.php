<?php

namespace MobileCart\CoreBundle\Service;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use MobileCart\CoreBundle\Constants\EntityConstants;

class FormHelperService
{
    /**
     * @param \Symfony\Component\Form\Form $form
     * @param \MobileCart\CoreBundle\Entity\CartEntityEAVInterface $entity
     * @return array
     */
    public function addCustomFields(\Symfony\Component\Form\Form &$form, \MobileCart\CoreBundle\Entity\CartEntityEAVInterface $entity)
    {
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
                    case EntityConstants::INPUT_SELECT:
                    case EntityConstants::INPUT_MULTISELECT:

                        $choices = [];
                        $options = $var->getItemVarOptions();
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
                            'multiple'  => ($var->getFormInput() == EntityConstants::INPUT_MULTISELECT),
                        ]);

                        $customFields[] = $name;

                        break;
                    case EntityConstants::INPUT_CHECKBOX:

                        $form->add($name, CheckboxType::class, [
                            'mapped' => false,
                            'required' => false,
                            'label' => $var->getName(),
                        ]);

                        $customFields[] = $name;
                        break;
                    case EntityConstants::INPUT_NUMBER:

                        $form->add($name, NumberType::class, [
                            'mapped' => false,
                            'label'  => $var->getName(),
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
                    if ($objectData['input'] == EntityConstants::INPUT_CHECKBOX) {
                        $value = (bool) $value;
                    }
                    $form->get($name)->setData($value);
                }
            }
        }

        return $customFields;
    }
}
