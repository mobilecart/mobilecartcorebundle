<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutBillingAddressType
 * @package MobileCart\CoreBundle\Form
 */
class CheckoutBillingAddressType extends AbstractType
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
        return $this->getCartService()->getCheckoutFormService()->getAllowGuestCheckout() && !$this->getCustomerId();
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
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
        }

        $builder
            ->add('billing_firstname', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'First Name',
            ])
            ->add('billing_lastname', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'Last Name',
            ])
            ->add('billing_company', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'Company',
            ])
            ->add('billing_street', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'Street',
            ])
            ->add('billing_street2', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'Street 2',
            ])
            ->add('billing_city', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'City',
            ])
            ->add('billing_region', TextType::class, [
                'attr' => ['class' => 'billing-input region-input'],
                'label' => 'State',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_postcode', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'Postal Code',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_country_id', ChoiceType::class, [
                'attr' => ['class' => 'billing-input country-input'],
                'label' => 'Country',
                'choices' => array_flip($this->getCountries()),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choices_as_values' => true,
            ])
            ->add('billing_phone', TextType::class, [
                'attr' => ['class' => 'billing-input'],
                'label' => 'Phone',
            ])
            ->add('is_shipping_same', CheckboxType::class, [
                'required' => false,
                'label' => 'Same as Shipping Address',
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
