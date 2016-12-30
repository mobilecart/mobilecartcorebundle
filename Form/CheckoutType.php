<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CheckoutType extends AbstractType
{
    protected $billingAddressForm;

    protected $shippingAddressForm;

    protected $shippingMethodForm;

    public function setBillingAddressForm($billingAddressForm)
    {
        $this->billingAddressForm = $billingAddressForm;
        return $this;
    }

    public function getBillingAddressForm()
    {
        return $this->billingAddressForm;
    }

    public function setShippingAddressForm($shippingAddressForm)
    {
        $this->shippingAddressForm = $shippingAddressForm;
        return $this;
    }

    public function getShippingAddressForm()
    {
        return $this->shippingAddressForm;
    }

    public function setShippingMethodForm($shippingMethodForm)
    {
        $this->shippingMethodForm = $shippingMethodForm;
        return $this;
    }

    public function getShippingMethodForm()
    {
        return $this->shippingMethodForm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billing_address', $this->getBillingAddressForm())
            ->add('shipping_address', $this->getShippingAddressForm())
        ;
    }

    public function getName()
    {
        return 'checkout';
    }
}
