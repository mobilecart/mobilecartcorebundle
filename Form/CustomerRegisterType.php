<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomerRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class, [
                'attr' => [
                    'placeholder' => 'First Name'
                ]
            ])
            ->add('last_name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Last Name'
                ]
            ])
            ->add('email', TextType::class, [
                'attr' => [
                    'placeholder' => 'Email'
                ]
            ])
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password', 'attr' => array('placeholder' => 'Password')),
                'second_options' => array('label' => 'Repeat Password', 'attr' => array('placeholder' => 'Confirm Password')),
                'mapped' => false,
            ));
    }

    public function getName()
    {
        return 'customer_register';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // for api calls
        ]);
    }
}
