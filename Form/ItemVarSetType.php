<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\EntityConstants;

class ItemVarSetType extends AbstractType
{
    // todo : wire this up

    protected $objectTypes = [];

    public function setObjectTypes(array $objectTypes)
    {
        $this->objectTypes = $objectTypes;
        return $this;
    }

    public function getObjectTypes()
    {
        return $this->objectTypes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('object_type', 'choice', [
                'choices' => EntityConstants::getEavObjects(),
            ]);
    }

    public function getName()
    {
        return 'item_var_set';
    }
}
