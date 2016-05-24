<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContentType extends AbstractType
{
    protected $customTemplates = [];

    public function setCustomTemplates(array $customTemplates)
    {
        $this->customTemplates = $customTemplates;
        return $this;
    }

    public function getCustomTemplates()
    {
        return $this->customTemplates;
    }

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
            //->add('meta_title', 'textarea', ['required'  => false])
            ->add('meta_keywords', 'textarea', ['required'  => false])
            ->add('meta_description', 'textarea', ['required'  => false])
            ->add('author', 'text')
            ->add('is_searchable', 'checkbox', ['required' => false])
            ->add('is_public', 'checkbox', ['required' => false])
            ->add('custom_template', 'choice', [
                'required' => false,
                'choices' => $this->getCustomTemplates(),
            ])
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
