<?php

namespace MobileCart\CoreBundle\EventListener\Contact;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ContactForm
 * @package MobileCart\CoreBundle\EventListener\Contact
 */
class ContactForm
{
    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $recaptchaKey;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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
     * @param string $formTypeClass
     * @return $this
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    /**
     * @param $recaptchaKey
     * @return $this
     */
    public function setRecaptchaKey($recaptchaKey)
    {
        $this->recaptchaKey = $recaptchaKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecaptchaKey()
    {
        return $this->recaptchaKey;
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
    public function onContactForm(CoreEvent $event)
    {
        $form = $this->getFormFactory()->create($this->getFormTypeClass(), new ArrayWrapper(), [
            'action' => $this->getRouter()->generate('cart_contact_post', []),
            'method' => 'POST',
        ]);
        $event->setForm($form);
    }
}
