<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent_id')
            ->add('name')
            ->add('code')
            ->add('weight')
            ->add('store')
        ;
    }

    public function getName()
    {
        return 'mobilecart_corebundle_productcategorytype';
    }
}
