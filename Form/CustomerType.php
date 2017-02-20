<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomerType extends AbstractType
{
    /**
     * @var array
     */
    protected $countries = [];

    /**
     * @param array $countries
     * @return $this
     */
    public function setCountries(array $countries)
    {
        $this->countries = $countries;
        return $this;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_enabled', 'checkbox', [
                'required' => false
            ])
            ->add('first_name', 'text')
            ->add('last_name', 'text')
            ->add('email', 'text',[
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_name', 'text')
            ->add('billing_phone')
            ->add('billing_street', 'text')
            ->add('billing_street2', 'text')
            ->add('billing_city', 'text')
            ->add('billing_region', 'text', [
                'attr' => [
                    'class' => 'region-input',
                ],
            ])
            ->add('billing_postcode', 'text')
            ->add('billing_country_id', 'choice', [
                'choices' => $this->getCountries(),
                'attr' => [
                    'class' => 'country-input',
                ],
            ])
            ->add('is_shipping_same')
            ->add('shipping_name', 'text', [
                'required' => 0,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_phone', 'text', [
                'required' => 0,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_street', 'text', [
                'required' => 0,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_street2', 'text', [
                'required' => 0,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_city', 'text', [
                'required' => 0,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_region', 'text', [
                'required' => 0,
                'attr' => ['class' => 'shipping-input region-input'],
            ])
            ->add('shipping_postcode', 'text', [
                'required' => 0,
                'attr' => ['class' => 'shipping-input'],
            ])
            ->add('shipping_country_id', 'choice', [
                'choices' => $this->getCountries(),
                'required' => 0,
                'attr' => [
                    'class' => 'country-input',
                ],
            ])
            ->add('is_locked', 'checkbox', [
                'required' => 0,
            ])
            ->add('is_password_expired', 'checkbox', [
                'required' => 0,
            ])
            ->add('is_expired', 'checkbox', [
                'required' => 0,
            ])
            ->add('api_key', 'text', [
                'required' => 0,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $customer = $event->getData();
            $form = $event->getForm();

            // check if the Customer object is "new"
            // If no data is passed to the form, the data is "null".
            // This should be considered a new "Customer"
            if (!$customer || !$customer->getId()) {

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

    public function getName()
    {
        return 'customer';
    }
}
