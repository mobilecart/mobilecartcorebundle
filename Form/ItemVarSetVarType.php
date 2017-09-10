<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ItemVarSetVarType
 * @package MobileCart\CoreBundle\Form
 */
class ItemVarSetVarType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item_var_set')
            ->add('item_var');
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'item_var_set_var';
    }
}
