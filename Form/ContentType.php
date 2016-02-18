<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContentType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('item_var_set')
            ->add('name', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('page_title', 'text', [
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
            ->add('sort_order', 'text', ['required'  => false])
            ->add('content', 'textarea', ['required'  => false])
            ->add('meta_title', 'textarea', ['required'  => false])
            ->add('meta_keywords', 'textarea', ['required'  => false])
            ->add('meta_description', 'textarea', ['required'  => false])
            ->add('author', 'text')
            ->add('is_searchable', 'checkbox', ['required' => false])
            ->add('is_public', 'checkbox', ['required' => false])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'content';
    }
}
