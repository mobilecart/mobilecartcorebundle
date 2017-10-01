<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Intl\Intl;

/**
 * Class CustomerProfileType
 * @package MobileCart\CoreBundle\Form
 */
class CustomerProfileType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        $allCountries = Intl::getRegionBundle()->getCountryNames();
        $allowedCountries = $this->getCartService()->getAllowedCountryIds();

        $countries = [];
        foreach($allowedCountries as $countryId) {
            $countries[$countryId] = $allCountries[$countryId];
        }

        return $countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', TextType::class)
            ->add('last_name', TextType::class)
            ->add('email', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_name', TextType::class, [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_company', TextType::class, [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_phone', TextType::class, [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_street', TextType::class, [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_street2', TextType::class, [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_city', TextType::class, [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_region', TextType::class, [
                'attr' => [
                    'class' => 'region-input billing-input',
                ],
            ])
            ->add('billing_postcode', TextType::class, [
                'attr' => [
                    'class' => 'billing-input',
                ]
            ])
            ->add('billing_country_id', ChoiceType::class, [
                'choices' => array_flip($this->getCountries()),
                'attr' => [
                    'class' => 'country-input billing-input',
                ],
                'choices_as_values' => true,
            ])
            ->add('is_shipping_same', CheckboxType::class)
            ->add('shipping_name', TextType::class, [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_company', TextType::class, [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_phone', TextType::class, [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_street', TextType::class, [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_street2', TextType::class, [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_city', TextType::class, [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_region', TextType::class, [
                'attr' => [
                    'class' => 'region-input shipping-input',
                ],
            ])
            ->add('shipping_postcode', TextType::class, [
                'attr' => [
                    'class' => 'shipping-input',
                ]
            ])
            ->add('shipping_country_id', ChoiceType::class, [
                'choices' => array_flip($this->getCountries()),
                'attr' => [
                    'class' => 'country-input shipping-input',
                ],
                'choices_as_values' => true,
            ])
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'mapped' => false,
            ));
        ;
    }

    public function getBlockPrefix()
    {
        return 'customer_profile';
    }
}
