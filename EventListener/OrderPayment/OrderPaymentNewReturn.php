<?php

namespace MobileCart\CoreBundle\EventListener\OrderPayment;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderPaymentNewReturn
 * @package MobileCart\CoreBundle\EventListener\OrderPayment
 */
class OrderPaymentNewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

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
     * @param CoreEvent $event
     */
    public function onOrderPaymentNewReturn(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\OrderPayment $entity */
        $entity = $event->getEntity();
        $event->setReturnData('entity', $entity);

        if ($entity->getOrder()) {
            $event->getForm()->get('base_amount')->setData($entity->getOrder()->getBaseTotal());
        }

        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('template_sections', []);

        /* todo: finish this

        $sections = $event->getReturnData('form_sections');
        $customSections = [];
        $serviceRequest = new \MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest();
        $services = $this->getPaymentService()->collectPaymentMethods($serviceRequest);
        if ($services) {
            foreach($services as $service) {
                $customSections[$service->getCode()] = ['form' => $service->getForm()];
            }
        }
        $sections['general']['custom_sections'] = $customSections;
        $event->setReturnData('form_sections', $sections); //*/

        $event->setResponse($this->getThemeService()->renderAdmin(
            'OrderPayment:new.html.twig',
            $event->getReturnData()
        ));
    }
}
