<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerRegisterConfirmSendEmail
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterConfirmSendEmail
{
    /**
     * @var string
     */
    protected $fromEmail = '';

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    protected $mailer;

    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param $fromEmail
     * @return $this
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerRegisterConfirm(CoreEvent $event)
    {
        if ($event->getSuccess()) {

            $recipient = $event->getRecipient()
                ? $event->getRecipient()
                : $event->getEntity()->getEmail();

            $subject = $event->getSubject()
                ? $event->getSubject()
                : 'Account Confirmed';

            $tplData = $event->getEntity()->getData();

            $tpl = 'Email:register_confirmed.html.twig';

            $body = $this->getThemeService()->renderView('email', $tpl, $tplData);

            try {

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->getFromEmail())
                    ->setTo($recipient)
                    ->setBody($body, 'text/html');

                $this->getMailer()->send($message);

            } catch(\Exception $e) {
                $event->addErrorMessage('An error occurred while sending email');
            }
        }

    }
}
