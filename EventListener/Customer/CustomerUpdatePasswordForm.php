<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Form\CustomerUpdatePasswordType;

/**
 * Class CustomerUpdatePasswordForm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerUpdatePasswordForm
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
    public function onCustomerUpdatePasswordForm(CoreEvent $event)
    {
        $formType = new CustomerUpdatePasswordType();

        $action = $this->getRouter()->generate('customer_update_password_post', [
            'id' => $event->getEntity()->getId(),
            'hash' => $event->getEntity()->getConfirmHash()
        ]);

        $event->setReturnData('form', $this->getFormFactory()->create($formType, $event->getEntity(), [
            'action' => $action,
            'method' => 'POST',
        ]));

        $event->setReturnData('form_sections', [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'password',
                ],
            ],
        ]);
    }
}
