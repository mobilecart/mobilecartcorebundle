<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $builder
            ->add('email', 'text', array(
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Email',
                )))
            ->add('password', 'password', array(
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Password'
                )));

        return $builder;
    }

    public function getName()
    {
        return 'customer_login';
    }
}
