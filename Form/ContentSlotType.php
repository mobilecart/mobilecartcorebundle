<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContentSlotType
 * @package MobileCart\CoreBundle\Form
 */
class ContentSlotType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent_id', TextType::class, ['required'  => false, 'mapped' => false])
            ->add('title', TextType::class, ['required'  => false])
            ->add('body_text', TextareaType::class, ['required'  => false])
            ->add('sort_order', TextType::class, ['required'  => false])
            ->add('content_type', TextType::class, ['required'  => false])
            ->add('url', TextType::class, ['required'  => false])
            ->add('embed_code', TextType::class, ['required'  => false])
            ->add('path', TextType::class, ['required'  => false])
            ->add('alt_text', TextType::class, ['required'  => false])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
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
