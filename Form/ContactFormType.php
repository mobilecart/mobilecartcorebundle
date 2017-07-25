<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'attr' => [
                    'placeholder' => 'Email'
                ],
                'mapped' => false,
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Name'
                ],
                'mapped' => false,
            ])
            ->add('phone', TextType::class, [
                'attr' => [
                    'placeholder' => 'Phone'
                ],
                'mapped' => false,
            ])
            ->add('message', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Message'
                ],
                'mapped' => false,
            ])
        ;
    }

    public function getName()
    {
        return 'contact';
    }

    public function getBlockPrefix()
    {
        return 'contact';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // for api calls
        ]);
    }
}
