<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\CustomerForgotPasswordType;

class CustomerForgotPasswordForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    protected $formFactory;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

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

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param Event $event
     */
    public function onCustomerForgotPasswordForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();

        $formType = new CustomerForgotPasswordType();
        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
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
        $event->setForm($form);
        $event->setReturnData($returnData);
    }
}
