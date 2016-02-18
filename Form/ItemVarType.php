<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Entity\ItemVar;

class ItemVarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('code', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('url_token')
            ->add('datatype', 'choice', ['choices' => ItemVar::$types])
            ->add('form_input', 'choice', ['choices' => ItemVar::$formInputs])
            ->add('is_required')
            ->add('is_displayed')
        ;
    }

    public function getName()
    {
        return 'item_var';
    }
}
