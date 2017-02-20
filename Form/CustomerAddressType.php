<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomerAddressType extends AbstractType
{
    /**
     * @var array
     */
    protected $countries = [];

    /**
     * @param array $countries
     * @return $this
     */
    public function setCountries(array $countries)
    {
        $this->countries = $countries;
        return $this;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('company', 'text')
            ->add('phone')
            ->add('street', 'text')
            ->add('street2', 'text')
            ->add('city', 'text')
            ->add('region', 'text', [
                'attr' => [
                    'class' => 'region-input',
                ],
            ])
            ->add('postcode', 'text')
            ->add('country_id', 'choice', [
                'choices' => $this->getCountries(),
                'attr' => [
                    'class' => 'country-input',
                ],
            ])
        ;
    }

    public function getName()
    {
        return 'customer_address';
    }
}
