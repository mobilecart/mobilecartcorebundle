<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutShippingAddressType extends AbstractType
{
    protected $countries;

    public function setCountries($countries)
    {
        $this->countries = $countries;
        return $this;
    }

    public function getCountries()
    {
        return $this->countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('is_shipping_same', 'checkbox', [
                'required' => 0,
                'label' => 'shipping.same'
            ])
            ->add('shipping_name', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.name',
            ])
            ->add('shipping_street', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.street',
            ])
            ->add('shipping_city', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.city',
            ])
            ->add('shipping_region', 'text', [
                'attr' => ['class' => 'shipping-input region-input'],
                'label' => 'shipping.region',
            ])
            ->add('shipping_postcode', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.postcode',
            ])
            ->add('shipping_country_id', 'choice', [
                'attr' => ['class' => 'shipping-input country-input'],
                'label' => 'shipping.country',
                'choices' => $this->getCountries(),
            ])
            ->add('shipping_phone', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.phone',
            ])
        ;
    }

    public function getName()
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
