<?php

namespace MobileCart\CoreBundle\EventListener\Contact;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use Symfony\Component\EventDispatcher\Event;

class ContactForm
{
    /**
     * @var string
     */
    protected $formTypeClass;

    protected $formFactory;

    /**
     * @var string
     */
    protected $recaptchaKey;

    /**
     * @var Event
     */
    protected $event;

    protected $router;

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

    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
        return $this;
    }

    public function getFormTypeClass()
    {
        return $this->formTypeClass;
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

    public function setRecaptchaKey($recaptchaKey)
    {
        $this->recaptchaKey = $recaptchaKey;
        return $this;
    }

    public function getRecaptchaKey()
    {
        return $this->recaptchaKey;
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function onContactForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $route = 'cart_contact_post';
        $params = [];
        $url = $this->getRouter()->generate($route, $params);

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), new ArrayWrapper(), [
            'action' => $url,
            'method' => 'POST',
        ]);

        $returnData['form'] = $form;
        $event->setReturnData($returnData);
    }
}
