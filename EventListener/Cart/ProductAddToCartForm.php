<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;

class ProductAddToCartForm
{

    protected $form_factory;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setFormFactory($formFactory)
    {
        $this->form_factory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->form_factory;
    }

    public function onProductAddToCartForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $data = [
            'id' => $event->getEntity()->getId(),
            'qty' => 1
        ];

        $options = [];
        $type = 'Symfony\Component\Form\Extension\Core\Type\FormType';
        $form = $this->getFormFactory()->createBuilder($type, $data, $options)
            ->add('id', 'hidden')
            ->add('qty', 'text')
            ->getForm();

        $event->setForm($form)
            ->setReturnData($returnData);
    }
}
