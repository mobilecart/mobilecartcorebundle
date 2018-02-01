<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerRegisterSendEmail
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterSendEmail
{

    protected $mailer;

    /**
     * @var string
     */
    protected $fromEmail = '';

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    protected $passwordEncoder;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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

    public function setSecurityPasswordEncoder($passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->passwordEncoder;
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
    public function onCustomerRegister(CoreEvent $event)
    {
        if ($event->getSuccess()) {

            $recipient = $event->getRecipient()
                ? $event->getRecipient()
                : $event->getEntity()->getEmail();

            $subject = $event->getSubject()
                ? $event->getSubject()
                : 'Account Registration';

            $route = 'customer_register_confirm';

            $url = $this->getRouter()->generate($route, [
                'id' => $event->getEntity()->getId(),
                'hash' => $event->getEntity()->getConfirmHash(),
            ]);

            $tplData = $event->getEntity()->getData();
            $tplData['url'] = $url;

            $tpl = 'Email:register_confirm.html.twig';

            $body = $this->getThemeService()->renderView('email', $tpl, $tplData);

            try {

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->getFromEmail())
                    ->setTo($recipient)
                    ->setBody($body, 'text/html');

                $this->getMailer()->send($message);

            } catch(\Exception $e) {
                $event->addWarningMessage('An error occurred while sending email');
            }
        }
    }
}
