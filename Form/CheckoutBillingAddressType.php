<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Service\CheckoutSessionService;

/**
 * Class CheckoutBillingAddressType
 * @package MobileCart\CoreBundle\Form
 */
class CheckoutBillingAddressType extends AbstractType
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
        return $this->getCheckoutSessionService()->getCartService();
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return (int) $this->getCartService()->getCustomerId();
    }

    /**
     * @return bool
     */
    public function getDisplayEmailInput()
    {
        return $this->getCheckoutSessionService()->getAllowGuestCheckout() && !$this->getCustomerId();
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

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->getDisplayEmailInput()) {

            $builder->add('email', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'email',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }

        $builder
            ->add('billing_name', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.name',
            ])
            ->add('billing_company', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.company',
            ])
            ->add('billing_street', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.street',
            ])
            ->add('billing_street2', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.street2',
            ])
            ->add('billing_city', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.city',
            ])
            ->add('billing_region', TextType::class, [
                'attr' => ['class' => 'billing-input region-input'],
                'label' => 'billing.region',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_postcode', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.postcode',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_country_id', ChoiceType::class, [
                'attr' => ['class' => 'billing-input country-input'],
                'label' => 'billing.country',
                'choices' => array_flip($this->getCountries()),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choices_as_values' => true,
            ])
            ->add('billing_phone', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'billing.phone',
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return CheckoutConstants::STEP_BILLING_ADDRESS;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
