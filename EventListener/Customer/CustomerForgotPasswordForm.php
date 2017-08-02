<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Form\CustomerForgotPasswordType;

/**
 * Class CustomerForgotPasswordForm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerForgotPasswordForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerForgotPasswordForm(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();

        $params = [];
        $route = 'customer_forgot_password';
        $action = $this->getRouter()->generate($route, $params);

        $formType = new CustomerForgotPasswordType();
        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $action,
            'method' => 'POST',
        ]);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'email',
                ],
            ],
        ];

        $returnData['form_sections'] = $formSections;
        $returnData['form'] = $form;
        $event->setReturnData($returnData);
    }
}
