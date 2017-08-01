<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ProductAddToCartForm
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ProductAddToCartForm
{

    protected $formFactory;

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
     * @param CoreEvent $event
     */
    public function onProductAddToCartForm(CoreEvent $event)
    {
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
