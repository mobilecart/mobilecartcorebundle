<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;

class CustomerRegister
{

    protected $event;

    protected $mailer;

    protected $fromEmail = '';

    protected $router;

    protected $passwordEncoder;

    protected $entityService;

    protected $themeService;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    public function getMailer()
    {
        return $this->mailer;
    }

    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    public function getFromEmail()
    {
        return $this->fromEmail;
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

    public function setSecurityPasswordEncoder($passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->passwordEncoder;
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function onCustomerRegister(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        // encode password, handle hash
        if (isset($formData['password']['first']) && $formData['password']['first']) {
            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $formData['password']['first']);
            $entity->setHash($encoded);
        }

        $confirmHash = md5(microtime());
        $entity->setConfirmHash($confirmHash);

        $this->getEntityService()->persist($entity);

        if ($entity->getId()) {
            $event->addSuccessMessage("You are Registered");
        }

        $route = 'customer_register_confirm';

        $urlData = [
            'id' => $entity->getId(),
            'hash' => $confirmHash,
        ];

        $url = $this->getRouter()->generate($route, $urlData);

        $tplData = array_merge($entity->getData(), [
            'url' => $url,
        ]);

        $tpl = 'Email:register_confirm.html.twig';

        $body = $this->getThemeService()->renderView('email', $tpl, $tplData);

        try {

            $message = \Swift_Message::newInstance()
                ->setSubject('Account Registration')
                ->setFrom($this->getFromEmail())
                ->setTo($entity->getEmail())
                ->setBody($body, 'text/html');

            $this->getMailer()->send($message);

        } catch(\Exception $e) {
            // todo : handle error
        }

        $event->setReturnData($returnData);
    }
}
