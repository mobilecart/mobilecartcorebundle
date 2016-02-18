<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('url_value')
            ->add('additional_price')
            ->add('is_in_stock');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mobilecart_corebundle_itemvaroption';
    }
}
