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
                'label' => 'shipping.same',
                //'mapped' => false,
            ])
            ->add('shipping_name', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.name',
                //'mapped' => false,
            ])
            ->add('shipping_company', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.company',
                //'mapped' => false,
            ])
            ->add('shipping_street', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.street',
                //'mapped' => false,
            ])
            ->add('shipping_street2', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.street2',
                //'mapped' => false,
            ])
            ->add('shipping_city', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.city',
                //'mapped' => false,
            ])
            ->add('shipping_region', 'text', [
                'attr' => ['class' => 'shipping-input region-input'],
                'label' => 'shipping.region',
                //'mapped' => false,
            ])
            ->add('shipping_postcode', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.postcode',
                //'mapped' => false,
            ])
            ->add('shipping_country_id', 'choice', [
                'attr' => ['class' => 'shipping-input country-input'],
                'label' => 'shipping.country',
                'choices' => $this->getCountries(),
                //'mapped' => false,
            ])
            ->add('shipping_phone', 'text', [
                'attr' => ['class' => 'shipping-input'],
                'label' => 'shipping.phone',
                //'mapped' => false,
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
