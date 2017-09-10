<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Entity\ItemVar;

/**
 * Class ItemVarType
 * @package MobileCart\CoreBundle\Form
 */
class ItemVarType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('code', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('url_token', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('datatype', ChoiceType::class, ['choices' => ItemVar::$types])
            ->add('form_input', ChoiceType::class, ['choices' => ItemVar::$formInputs])
            ->add('is_required')
            ->add('is_displayed')
            ->add('sort_order')
            ->add('is_sortable')
            ->add('is_facet')
            ->add('is_searchable')
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'item_var';
    }
}
