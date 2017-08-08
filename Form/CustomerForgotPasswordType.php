<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class CustomerForgotPasswordType
 * @package MobileCart\CoreBundle\Form
 */
class CustomerForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    
        $builder
            ->add('email', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'placeholder' => 'Email',
                ]
            ]);
    }

    public function getName()
    {
        return 'customer_forgot_password';
    }

    public function getBlockPrefix()
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
