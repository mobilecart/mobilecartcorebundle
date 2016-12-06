<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\EntityConstants;

class CategoryType extends AbstractType
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
            ->add('custom_template', 'choice', [
                'required' => false,
                'choices' => $this->getCustomTemplates(),
            ])
            ->add('display_mode', 'choice', [
                'required' => false,
                'choices' => EntityConstants::getDisplayModes()
            ])
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
