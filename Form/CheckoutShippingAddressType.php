<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Service\CheckoutSessionService;

class CheckoutShippingAddressType extends AbstractType
{
    /**
     * @var CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @param CheckoutSessionService $checkoutSessionService
     * @return $this
     */
    public function setCheckoutSessionService(CheckoutSessionService $checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    /**
     * @return CheckoutSessionService
     */
    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getCheckoutSessionService()->getCartSessionService()->getCartService();
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
            ->add('is_shipping_same', CheckboxType::class, [
                'required' => 0,
                'label' => 'shipping.same',
                //'mapped' => false,
            ])
            ->add('shipping_name', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.name',
                //'mapped' => false,
            ])
            ->add('shipping_company', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.company',
                //'mapped' => false,
            ])
            ->add('shipping_street', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.street',
                //'mapped' => false,
            ])
            ->add('shipping_street2', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.street2',
                //'mapped' => false,
            ])
            ->add('shipping_city', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.city',
                //'mapped' => false,
            ])
            ->add('shipping_region', TextType::class, [
                'attr' => ['class' => 'shipping-input region-input'],
                'label' => 'shipping.region',
                //'mapped' => false,
            ])
            ->add('shipping_postcode', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.postcode',
                //'mapped' => false,
            ])
            ->add('shipping_country_id', ChoiceType::class, [
                'attr' => ['class' => 'shipping-input country-input'],
                'label' => 'shipping.country',
                'choices' => array_flip($this->getCountries()),
                //'mapped' => false,
                'choices_as_values' => true,
            ])
            ->add('shipping_phone', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.phone',
                //'mapped' => false,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return CheckoutConstants::STEP_SHIPPING_ADDRESS;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
