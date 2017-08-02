<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Form\CustomerRegisterType;

/**
 * Class CustomerRegisterForm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

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
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
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
    public function onCustomerRegisterForm(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $entity = $event->getEntity();

        $params = [];
        $route = 'customer_register_post';
        $action = $this->getRouter()->generate($route, $params);

        $formType = new CustomerRegisterType();
        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $action,
            'method' => 'POST',
        ]);

        $formSections = [
            'general' => [
                'label' => 'Register',
                'id' => 'general',
                'fields' => [
                    'first_name',
                    'last_name',
                    'email',
                    'password',
                ],
            ],
        ];

        $returnData['form_sections'] = $formSections;
        $returnData['form'] = $form;

        $event->setReturnData($returnData);
    }
}
