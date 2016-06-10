<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $builder
            ->add('email', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function getName()
    {
        return 'customer_forgot_password';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // for api calls
        ]);
    }
}
