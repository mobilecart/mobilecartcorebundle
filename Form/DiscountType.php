<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use MobileCart\CartComponentBundle\CartComponent\Discount;

class DiscountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('priority', 'integer')
            ->add('value', 'number')
            ->add('applied_as', 'choice', [
                'choices' => [
                    Discount::$asFlat => 'Flat',
                    Discount::$asPercent => 'Percentage',
                ],
                'attr' => [
                    'class' => 'discount-condition',
                ]
            ])
            ->add('applied_to', 'choice', [
                'choices' => [
                    Discount::$toSpecified => 'Specified',
                    Discount::$toItems => 'All Products',
                    Discount::$toShipments => 'All Shipments',
                ],
                'attr' => [
                    'class' => 'discount-condition',
                ]
            ])
            ->add('is_compound', 'checkbox', [
                'required' => false,
                ])
            ->add('is_pre_tax', 'checkbox', [
                'required' => false,
                ])
            ->add('is_auto', 'checkbox', [
                'required' => false,
                ])
            ->add('is_stopper', 'checkbox', [
                'required' => false,
                ])
            ->add('is_max_per_item', 'checkbox', [
                'required' => false,
                ])
            ->add('is_proportional', 'checkbox', [
                'required' => false,
                ])
            ->add('start_time', 'text', [
                'required' => false,
                ])
            ->add('end_time', 'text', [
                'required' => false,
                ])
            ->add('max_amount', 'number', [
                'required' => false,
                ])
            ->add('max_qty', 'number', [
                'required' => false,
                ])
            ->add('coupon_code', 'text', [
                'required' => false,
                ])
            ->add('pre_conditions', 'text', [
                'required' => false,
                ])
            ->add('target_conditions', 'text', [
                'required' => false,
                ])
                ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MobileCart\CoreBundle\Entity\Discount',
        ));
    }

    public function getName()
    {
        return 'discount';
    }
}
