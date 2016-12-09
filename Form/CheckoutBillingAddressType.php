<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutBillingAddressType extends AbstractType
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
            ->add('email', 'text', [
                'attr' => ['class' => 'billing-input'],
                'label' => 'email',
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_name', 'text', [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.name',
            ])
            ->add('billing_company', 'text', [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.company',
            ])
            ->add('billing_street', 'text', [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.street',
            ])
            ->add('billing_city', 'text', [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.city',
            ])
            ->add('billing_region', 'text', [
                'attr' => ['class' => 'billing-input region-input'],
                'label' => 'billing.region',
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_postcode', 'text', [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.postcode',
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_country_id', 'choice', [
                'attr' => ['class' => 'billing-input country-input'],
                'label' => 'billing.country',
                'choices' => $this->getCountries(),
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_phone', 'text', [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.phone',
            ])
        ;
    }

    public function getName()
    {
        return CheckoutConstants::STEP_BILLING_ADDRESS;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
