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
//            ->add('type', 'hidden')
//            ->add('weight') // todo: add column to product model
            ->add('qty', 'number', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('is_taxable', 'checkbox', ['required' => 0])
            ->add('is_enabled', 'checkbox', ['required' => 0])
            ->add('is_qty_managed', 'checkbox', ['required' => 0])
            ->add('is_discountable', 'checkbox', ['required' => 0])
            ->add('is_in_stock', 'checkbox', ['required' => 0])
            ->add('is_on_sale', 'checkbox', ['required' => 0])
            ->add('is_new', 'checkbox', ['required' => 0])
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

        // todo : pass this in from a listener and service
        $choices = \MobileCart\CoreBundle\Entity\Product::$stockTypes;
        $builder->add('stock_type', 'choice', [
            'choices'   => $choices,
            'required'  => false
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'product';
    }
}
