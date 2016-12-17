<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
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

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('json', 'hidden')
            ->add('billing_name', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_phone')
            ->add('billing_street', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_city', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_region', 'text', [
                'attr' => [
                    'class' => 'region-input',
                ],
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_postcode', 'text', [
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('billing_country_id', 'choice', [
                'choices' => $this->getCountries(),
                'attr' => [
                    'class' => 'country-input',
                ],
                'required' => 1,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'MobileCart\CoreBundle\Entity\Order'
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'order';
    }
}
