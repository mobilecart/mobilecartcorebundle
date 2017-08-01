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
    protected $formTypeClass;

    protected $formFactory;

    /**
     * @var string
     */
    protected $recaptchaKey;

    protected $router;

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

    public function onContactForm(CoreEvent $event)
    {
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
