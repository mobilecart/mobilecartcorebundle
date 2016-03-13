<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Entity\ItemVar;

class ItemVarSetVarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('item_var_set')
            ->add('item_var')
        ;
    }

    public function getName()
    {
        return 'item_var_set_var';
    }
}
