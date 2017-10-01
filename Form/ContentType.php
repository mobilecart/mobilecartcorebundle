<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ContentType
 * @package MobileCart\CoreBundle\Form
 */
class ContentType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfigService;

    /**
     * @param $themeConfigService
     * @return $this
     */
    public function setThemeConfigService(\MobileCart\CoreBundle\Service\ThemeConfig $themeConfigService)
    {
        $this->themeConfigService = $themeConfigService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeConfig
     */
    public function getThemeConfigService()
    {
        return $this->themeConfigService;
    }

    /**
     * @return array
     */
    public function getCustomTemplates()
    {
        return $this->getThemeConfigService()->getObjectTypeTemplates(EntityConstants::CONTENT);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('item_var_set')
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('page_title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('sort_order', TextType::class, ['required'  => false])
            ->add('content', TextareaType::class, ['required'  => false])
            //->add('meta_title', TextareaType::class, ['required'  => false])
            ->add('meta_keywords', TextareaType::class, ['required'  => false])
            ->add('meta_description', TextareaType::class, ['required'  => false])
            ->add('author', TextType::class)
            ->add('is_searchable', CheckboxType::class, ['required' => false])
            ->add('is_public', CheckboxType::class, ['required' => false])
            ->add('custom_template', ChoiceType::class, [
                'required' => false,
                'choices' => array_flip($this->getCustomTemplates()),
                'choices_as_values' => true,
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'content';
    }
}
