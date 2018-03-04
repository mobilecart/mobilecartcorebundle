<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class AdminUserType
 * @package MobileCart\CoreBundle\Form
 */
class AdminUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_enabled', CheckboxType::class, [
                'required' => false
            ])
            ->add('email', TextType::class,[
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('firstname', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastname', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('is_locked', CheckboxType::class, [
                'required' => false,
            ])
            ->add('is_password_expired', CheckboxType::class, [
                'required' => false,
            ])
            ->add('is_expired', CheckboxType::class, [
                'required' => false,
            ])
            ->add('api_key', TextType::class, [
                'required' => false,
            ]);

        // todo : ensure the validation messages for this make it into flash messages eg "Cannot be blank"
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            $form = $event->getForm();

            // check if the Entity is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new Entity
            if (!$entity || !$entity->getId()) {

                $form->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]);

            } else {

                $form->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => false,
                    'first_options'  => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                    'mapped' => false,
                ]);
            }
        });
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return EntityConstants::ADMIN_USER;
    }
}
