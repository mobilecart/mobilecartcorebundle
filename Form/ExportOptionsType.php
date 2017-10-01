<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ExportOptionsType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\ExportService
     */
    protected $exportService;

    /**
     * @param $exportService
     * @return $this
     */
    public function setExportService($exportService)
    {
        $this->exportService = $exportService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ExportService
     */
    public function getExportService()
    {
        return $this->exportService;
    }

    public function getExportOptions()
    {
        $options = [];
        $exportOptions = $this->getExportService()->getExportOptions();

        if ($exportOptions) {
            foreach($exportOptions as $option) {
                $options[$option->getKey()] = $option->getLabel();
            }
        }

        return $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('export_option', ChoiceType::class, [
                'choices' => array_flip($this->getExportOptions()),
                'mapped' => false,
                'choices_as_values' => true,
            ])
            ->add('start_date', DateType::class, [
                'mapped' => false,
            ])
            ->add('end_date', DateType::class, [
                'mapped' => false,
            ]);
    }

    public function getBlockPrefix()
    {
        return 'export_options';
    }
}
