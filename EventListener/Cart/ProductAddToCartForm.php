<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;

class ProductAddToCartForm
{

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
    public function onProductAddToCartForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

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
