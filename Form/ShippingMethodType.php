<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ShippingMethodType
 * @package MobileCart\CoreBundle\Form
 */
class ShippingMethodType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @param \MobileCart\CoreBundle\Service\CurrencyService $currencyService
     * @return $this
     */
    public function setCurrencyService(\MobileCart\CoreBundle\Service\CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->getCurrencyService()->getBaseCurrency();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('company', TextType::class, [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('method', TextType::class, [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('price', MoneyType::class, [
                'currency' => $this->getCurrency(),
            ])
            ->add('min_days', NumberType::class)
            ->add('max_days', NumberType::class)
            ->add('is_taxable', CheckboxType::class, [
                'required' => 0,
            ])
            ->add('is_discountable', CheckboxType::class, [
                'required' => 0,
            ])
            ->add('is_price_dynamic', CheckboxType::class, [
                'required' => 0,
            ])
            ->add('pre_conditions', TextType::class, [
                'required' => 0,
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'shipping_method';
    }
}
