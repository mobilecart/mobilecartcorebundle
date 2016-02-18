<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\EntityConstants;

class UrlRewriteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('object_type', 'choice', [
                'choices' => EntityConstants::getUrlRewriteObjects(),
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('object_action', 'choice', [
                'choices' => EntityConstants::getUrlRewriteActions(),
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('request_uri', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('params_json', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ]) // javascript widget
            ->add('is_redirect')
            ->add('redirect_url')
        ;
    }

    public function getName()
    {
        return 'url_rewrite';
    }
}
