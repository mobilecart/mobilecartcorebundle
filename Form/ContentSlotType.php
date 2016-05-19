<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentSlotType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent', 'text', ['required'  => false])
            ->add('title', 'text', ['required'  => false])
            ->add('body_text', 'textarea', ['required'  => false])
            ->add('sort_order', 'text', ['required'  => false])
            ->add('content_type', 'text', ['required'  => false])
            ->add('url', 'text', ['required'  => false])
            ->add('embed_code', 'text', ['required'  => false])
            ->add('path', 'text', ['required'  => false])
            ->add('alt_text', 'text', ['required'  => false])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'content_slot';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // for api calls
        ]);
    }
}
