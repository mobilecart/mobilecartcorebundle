<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutShippingMethodType extends AbstractType
{
    protected $shippingMethods = [];

    protected $defaultValue = '';

    public function setShippingMethods(array $shippingMethods)
    {
        $this->shippingMethods = $shippingMethods;
        return $this;
    }

    public function getShippingMethods()
    {
        return $this->shippingMethods;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shipping_method', 'choice', [
                'label' => 'shipping.method',
                'choices' => $this->getShippingMethods(),
                'data' => $this->getDefaultValue(),
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function getName()
    {
        return CheckoutConstants::STEP_SHIPPING_METHOD;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
