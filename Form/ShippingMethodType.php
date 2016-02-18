<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ShippingMethodType extends AbstractType
{

    /**
     * @var string
     */
    protected $currency = 'USD';

    /**
     * @param $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('company', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('method', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('price', 'money', [
                'currency' => $this->getCurrency(),
            ])
            ->add('min_days', 'number')
            ->add('max_days', 'number')
            ->add('is_taxable', 'checkbox', [
                'required' => 0,
            ])
            ->add('is_discountable', 'checkbox', [
                'required' => 0,
            ])
            ->add('is_price_dynamic', 'checkbox', [
                'required' => 0,
            ])
            ->add('pre_conditions', 'text', [
                'required' => 0,
            ])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'shipping_method';
    }
}
