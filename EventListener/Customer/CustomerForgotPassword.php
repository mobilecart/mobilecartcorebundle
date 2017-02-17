<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;

class CustomerForgotPassword
{
    protected $securityPasswordEncoder;

    protected $mailer;

    protected $router;

    protected $fromEmail;

    protected $themeService;

    protected $entityService;

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

    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    public function getMailer()
    {
        return $this->mailer;
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

    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    public function getFromEmail()
    {
        return $this->fromEmail;
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

    public function setSecurityPasswordEncoder($encoder)
    {
        $this->securityPasswordEncoder = $encoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->securityPasswordEncoder;
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

    public function onCustomerForgotPassword(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();
        $request = $event->getRequest();

        $confirmHash = md5(microtime());
        $plaintext = substr($confirmHash, 0, 8);

        if ($event->getEmailPassword()) {

            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $plaintext);
            $entity->setHash($encoded);

            $this->getEntityService()->persist($entity);

            $tplData = [
                'password' => $plaintext,
            ];

            $tpl = 'Email:password_reset.html.twig';
            $body = $this->getThemeService()->renderView('email', $tpl, $tplData);

            try {

                $message = \Swift_Message::newInstance()
                    ->setSubject('Password Reset')
                    ->setFrom($this->getFromEmail())
                    ->setTo($entity->getEmail())
                    ->setBody($body, 'text/html');

                $this->getMailer()->send($message);

            } catch(\Exception $e) {
                // todo : handle error
            }

        } else {

            $entity->setConfirmHash($confirmHash);
            $this->getEntityService()->persist($entity);

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
                    ->setSubject('Password Reset')
                    ->setFrom($this->getFromEmail())
                    ->setTo($entity->getEmail())
                    ->setBody($body, 'text/html');

                $this->getMailer()->send($message);

            } catch(\Exception $e) {
                // todo : handle error
            }
        }

        $event->setReturnData($returnData);
    }
}
