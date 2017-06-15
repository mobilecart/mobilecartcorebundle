<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductType extends AbstractType
{
    /**
     * @var string
     */
    protected $currency = 'USD';

    protected $customTemplates = [];

    /**
     * @param $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

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
            ->add('sku', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('price', 'money', [
                'currency' => $this->getCurrency(),
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
//            ->add('type', 'hidden') // type is handled in controller logic
            ->add('source_address_key', 'text', ['required' => 0, 'label' => 'Warehouse Code'])
            ->add('weight', 'text', ['required' => 0])
            ->add('weight_unit', 'choice', [
                'required' => 0,
                'choices' => [
                    'lb' => 'LB',
                    'oz' => 'Ounce',
                    'kg' => 'Kilogram',
                    'g' => 'Gram',
                ]
            ])
            ->add('width', 'text', ['required' => 0])
            ->add('height', 'text', ['required' => 0])
            ->add('length', 'text', ['required' => 0])
            ->add('measure_unit', 'choice', [
                'required' => 0,
                'choices' => [
                    'in' => 'Inch',
                    'ft' => 'Foot',
                    'cm' => 'Centimeter',
                    'm' => 'Meter',
                ]
            ])
            ->add('qty', 'number', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('qty_unit', 'choice', [
                'required' => 0,
                'choices' => [
                    'EA' => 'Each',
                    'CASE' => 'Case',
                    '10-Pack' => '10 Pack',
                ]
            ])
            ->add('min_qty', 'number', ['required' => 0])
            ->add('is_enabled', 'checkbox', ['required' => 0])
            ->add('is_public', 'checkbox', ['required' => 0])
            ->add('is_taxable', 'checkbox', ['required' => 0])
            ->add('is_qty_managed', 'checkbox', ['required' => 0])
            ->add('is_discountable', 'checkbox', ['required' => 0])
            ->add('is_in_stock', 'checkbox', ['required' => 0])
            ->add('can_backorder', 'checkbox', ['required' => 0])
            ->add('custom_search', 'textarea',['required' => 0])
            ->add('name', 'text', [
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
            ->add('page_title')
            ->add('meta_title', 'textarea', ['required'  => false])
            ->add('meta_keywords', 'textarea', ['required'  => false])
            ->add('meta_description', 'textarea', [
                'required' => false,
            ])
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
        return 'product';
    }
}
