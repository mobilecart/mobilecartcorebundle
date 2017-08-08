<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * Class CustomerLoginType
 * @package MobileCart\CoreBundle\Form
 */
class CustomerLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $builder
            ->add('email', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Email',
                ]
            ])->add('password', PasswordType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Password'
                ]
            ]);

        return $builder;
    }

    public function getBlockPrefix()
    {
        return 'customer_login';
    }
}
