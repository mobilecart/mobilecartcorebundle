<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CategoryType
 * @package MobileCart\CoreBundle\Form
 */
class CategoryType extends AbstractType
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
        return $this->getThemeConfigService()->getObjectTypeTemplates(EntityConstants::CATEGORY);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent_category')
            ->add('name', TextType::class,[
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
            ->add('page_title', TextType::class)
            ->add('content', TextareaType::class, ['required' => false])
            ->add('meta_title', TextareaType::class, ['required' => false])
            ->add('meta_keywords', TextareaType::class, ['required' => false])
            ->add('meta_description', TextareaType::class, ['required' => false])
            ->add('sort_order', TextType::class, ['required' => false])
            ->add('custom_template', ChoiceType::class, [
                'required' => false,
                'choices' => $this->getCustomTemplates(),
            ])
            ->add('display_mode', ChoiceType::class, [
                'required' => false,
                'choices' => EntityConstants::getDisplayModes()
            ])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'category';
    }
}
