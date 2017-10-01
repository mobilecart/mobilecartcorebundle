<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ItemVarOptionType
 * @package MobileCart\CoreBundle\Form
 */
class ItemVarOptionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item_var')
            ->add('value')
            ->add('sort_order')
            ->add('url_value')
            ->add('additional_price')
            ->add('is_in_stock')
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'item_var_option';
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
