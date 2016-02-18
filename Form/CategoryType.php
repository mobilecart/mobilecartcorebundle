<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CategoryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent_category')
            ->add('name', 'text',[
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('slug', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('page_title')
            ->add('content', 'textarea', ['required' => false])
            ->add('meta_title', 'textarea', ['required' => false])
            ->add('meta_keywords', 'textarea', ['required' => false])
            ->add('meta_description', 'textarea', ['required' => false])
            ->add('sort_order', 'text', ['required' => false])
            //->add('item_var_set')
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'category';
    }
}
