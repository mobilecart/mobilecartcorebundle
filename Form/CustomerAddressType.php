<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Service\CartService;

class CustomerAddressType extends AbstractType
{
    /**
     * @var CartService $cartService
     */
    protected $cartService;

    /**
     * @param CartService $cartService
     * @return $this
     */
    public function setCartService(CartService $cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return CartService
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
            ->add('name', TextType::class)
            ->add('company', TextType::class)
            ->add('phone')
            ->add('street', TextType::class)
            ->add('street2', TextType::class)
            ->add('city', TextType::class)
            ->add('region', TextType::class, [
                'attr' => [
                    'class' => 'region-input',
                ],
            ])
            ->add('postcode', TextType::class)
            ->add('country_id', ChoiceType::class, [
                'choices' => $this->getCountries(),
                'attr' => [
                    'class' => 'country-input',
                ],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'customer_address';
    }
}
