<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class UrlRewriteType
 * @package MobileCart\CoreBundle\Form
 */
class UrlRewriteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('object_type', ChoiceType::class, [
                'choices' => array_flip(EntityConstants::getUrlRewriteObjects()),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choices_as_values' => true,
            ])
            ->add('object_action', ChoiceType::class, [
                'choices' => array_flip(EntityConstants::getUrlRewriteActions()),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'choices_as_values' => true,
            ])
            ->add('request_uri', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('params_json', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]) // javascript widget
            ->add('is_redirect')
            ->add('redirect_url')
        ;
    }

    public function getBlockPrefix()
    {
        return 'url_rewrite';
    }
}
