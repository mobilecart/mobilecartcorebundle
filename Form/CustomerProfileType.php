<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotBlank;

class CustomerProfileType extends AbstractType
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
            ->add('first_name')
            ->add('last_name')
            ->add('email', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_name', 'text', [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_company', 'text', [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_phone', 'text', [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_street', 'text', [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_city', 'text', [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_region', 'text', [
                'attr' => [
                    'class' => 'region-input billing-input',
                ],
            ])
            ->add('billing_postcode')
            ->add('billing_country_id', 'choice', [
                'choices' => $this->getCountries(),
                'attr' => [
                    'class' => 'country-input billing-input',
                ],
            ])
            ->add('is_shipping_same')
            ->add('shipping_name', 'text', [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_company', 'text', [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_phone', 'text', [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_street')
            ->add('shipping_city', 'text', [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_region', 'text', [
                'attr' => [
                    'class' => 'region-input shipping-input',
                ],
            ])
            ->add('shipping_postcode', 'text', [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_country_id', 'choice', [
                'choices' => $this->getCountries(),
                'attr' => [
                    'class' => 'country-input shipping-input',
                ],
            ])
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
                'mapped' => false,
            ));
        ;
    }

    public function getName()
    {
        return 'customer_profile';
    }
}
