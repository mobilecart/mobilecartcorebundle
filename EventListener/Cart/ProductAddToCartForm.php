<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ProductAddToCartForm
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ProductAddToCartForm
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

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
     * @param CoreEvent $event
     */
    public function onProductAddToCartForm(CoreEvent $event)
    {
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

        $event->setReturnData('form', $form);
    }
}
