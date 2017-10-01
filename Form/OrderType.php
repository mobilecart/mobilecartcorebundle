<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Intl\Intl;

/**
 * Class OrderType
 * @package MobileCart\CoreBundle\Form
 */
class OrderType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

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
     * @param \MobileCart\CoreBundle\Service\OrderService $orderService
     * @return $this
     */
    public function setOrderService(\MobileCart\CoreBundle\Service\OrderService $orderService)
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
     * @return array
     */
    public function getStatusOptions()
    {
        $statusOptions = [];
        if ($this->getOrderService()->getStatusOptions()) {
            foreach($this->getOrderService()->getStatusOptions() as $option) {
                $statusOptions[$option['key']] = $option['label'];
            }
        }
        return $statusOptions;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => array_flip($this->getStatusOptions()),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choices_as_values' => true,
            ])
            ->add('json', HiddenType::class)
            ->add('billing_name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_phone')
            ->add('billing_street', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_street2', TextType::class)
            ->add('billing_city', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_region', TextType::class, [
                'attr' => [
                    'class' => 'region-input',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_postcode', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_country_id', ChoiceType::class, [
                'choices' => array_flip($this->getCountries()),
                'attr' => [
                    'class' => 'country-input',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choices_as_values' => true,
            ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'order';
    }
}
