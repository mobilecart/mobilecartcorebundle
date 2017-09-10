<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ItemVarSetType
 * @package MobileCart\CoreBundle\Form
 */
class ItemVarSetType extends AbstractType
{
    // todo : wire this up

    /**
     * @var array
     */
    protected $objectTypes = [];

    /**
     * @param array $objectTypes
     * @return $this
     */
    public function setObjectTypes(array $objectTypes)
    {
        $this->objectTypes = $objectTypes;
        return $this;
    }

    /**
     * @return array
     */
    public function getObjectTypes()
    {
        return $this->objectTypes;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('object_type', ChoiceType::class, [
                'choices' => EntityConstants::getEavObjects(),
            ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'item_var_set';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // for api calls
        ]);
    }
}
