<?php

namespace MobileCart\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class OrderPaymentType
 * @package MobileCart\CoreBundle\Form
 */
class OrderPaymentType extends AbstractType
{
    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @param \MobileCart\CoreBundle\Service\PaymentService $paymentService
     * @return $this
     */
    public function setPaymentService(\MobileCart\CoreBundle\Service\PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\PaymentService
     */
    public function getPaymentService()
    {
        return $this->paymentService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $serviceOptions = [];
        $serviceRequest = new \MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest();
        $services = $this->getPaymentService()->collectPaymentMethods($serviceRequest);
        if ($services) {
            foreach($services as $service) {
                $serviceOptions[$service->getCode()] = $service->getLabel();
            }
        }

        $builder
            ->add('base_amount', TextType::class, ['required'  => true])
            ->add('code', ChoiceType::class, ['required'  => true, 'choices' => $serviceOptions])
        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'order_payment';
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
