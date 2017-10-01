<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ProductType
 * @package MobileCart\CoreBundle\Form
 */
class ProductType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeConfig
     */
    protected $themeConfig;

    /**
     * @param \MobileCart\CoreBundle\Service\CurrencyService $currencyService
     * @return $this
     */
    public function setCurrencyService(\MobileCart\CoreBundle\Service\CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->getCurrencyService()->getBaseCurrency();
    }

    /**
     * @param \MobileCart\CoreBundle\Service\ThemeConfig $themeConfig
     * @return $this
     */
    public function setThemeConfig(\MobileCart\CoreBundle\Service\ThemeConfig $themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeConfig
     */
    public function getThemeConfig()
    {
        return $this->themeConfig;
    }

    /**
     * @return array
     */
    public function getCustomTemplates()
    {
        return $this->getThemeConfig()->getObjectTypeTemplates(EntityConstants::PRODUCT);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('price', MoneyType::class, [
                'currency' => $this->getCurrency(),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('source_address_key', TextType::class, ['required' => false, 'label' => 'Warehouse Code'])
            ->add('weight', TextType::class, ['required' => false])
            ->add('weight_unit', ChoiceType::class, [
                'required' => false,
                'choices' => array_flip([
                    'lb' => 'LB',
                    'oz' => 'Ounce',
                    'kg' => 'Kilogram',
                    'g' => 'Gram',
                ]),
                'choices_as_values' => true,
            ])
            ->add('width', TextType::class, ['required' => false])
            ->add('height', TextType::class, ['required' => false])
            ->add('length', TextType::class, ['required' => false])
            ->add('measure_unit', ChoiceType::class, [
                'required' => false,
                'choices' => array_flip([
                    'in' => 'Inch',
                    'ft' => 'Foot',
                    'cm' => 'Centimeter',
                    'm' => 'Meter',
                ]),
                'choices_as_values' => true,
            ])
            ->add('qty', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('qty_unit', ChoiceType::class, [
                'required' => false,
                'choices' => array_flip([
                    'EA' => 'Each',
                    'CASE' => 'Case',
                    '10-Pack' => '10 Pack',
                ]),
                'choices_as_values' => true,
            ])
            ->add('upc', TextType::class, ['required' => false])
            ->add('min_qty', NumberType::class, ['required' => false])
            ->add('is_enabled', CheckboxType::class, ['required' => false])
            ->add('is_public', CheckboxType::class, ['required' => false])
            ->add('is_taxable', CheckboxType::class, ['required' => false])
            ->add('is_qty_managed', CheckboxType::class, ['required' => false])
            ->add('is_discountable', CheckboxType::class, ['required' => false])
            ->add('is_in_stock', CheckboxType::class, ['required' => false])
            ->add('is_flat_shipping', CheckboxType::class, ['required' => false])
            ->add('flat_shipping_price', TextType::class, ['required' => false])
            ->add('can_backorder', CheckboxType::class, ['required' => false])
            ->add('custom_search', TextareaType::class, ['required' => false])
            ->add('name', TextType::class, [
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
            ->add('page_title')
            ->add('meta_title', TextareaType::class, ['required'  => false])
            ->add('meta_keywords', TextareaType::class, ['required'  => false])
            ->add('meta_description', TextareaType::class, [
                'required' => false,
            ])
            ->add('custom_template', ChoiceType::class, [
                'required' => false,
                'choices' => array_flip($this->getCustomTemplates()),
                'choices_as_values' => true,
            ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'product';
    }
}
