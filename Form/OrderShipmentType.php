<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Class OrderShipmentType
 * @package MobileCart\CoreBundle\Form
 */
class OrderShipmentType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', TextType::class, ['required'  => false])
            ->add('method', TextType::class, ['required'  => false])
            ->add('tracking', TextType::class, ['required'  => false])
            ->add('base_price', TextType::class, ['required'  => false])
            ->add('adjust_totals', CheckboxType::class, ['required' => false, 'mapped' => false])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'order_shipment';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // for api calls
        ]);
    }
}
