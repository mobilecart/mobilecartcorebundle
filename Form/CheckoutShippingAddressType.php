<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Service\CheckoutSessionService;

/**
 * Class CheckoutShippingAddressType
 * @package MobileCart\CoreBundle\Form
 */
class CheckoutShippingAddressType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @param $orderService
     * @return $this
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
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
            ->add('shipping_firstname', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'First Name',
            ])
            ->add('shipping_lastname', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'Last Name',
            ])
            ->add('shipping_company', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'Company',
            ])
            ->add('shipping_street', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'Street',
            ])
            ->add('shipping_street2', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'Street2',
            ])
            ->add('shipping_city', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'City',
            ])
            ->add('shipping_region', TextType::class, [
                'attr' => ['class' => 'shipping-input region-input'],
                'label' => 'State',
            ])
            ->add('shipping_postcode', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'Zip',
            ])
            ->add('shipping_country_id', ChoiceType::class, [
                'attr' => ['class' => 'shipping-input country-input'],
                'label' => 'Country',
                'choices' => array_flip($this->getCountries()),
                'choices_as_values' => true,
            ])
            ->add('shipping_phone', TextType::class, [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'Phone',
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
