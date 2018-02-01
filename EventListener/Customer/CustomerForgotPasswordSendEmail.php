<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerForgotPasswordSendEmail
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerForgotPasswordSendEmail
{
    protected $securityPasswordEncoder;

    protected $mailer;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $fromEmail;

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

    public function setSecurityPasswordEncoder($encoder)
    {
        $this->securityPasswordEncoder = $encoder;
        return $this;
    }

    public function getSecurityPasswordEncoder()
    {
        return $this->securityPasswordEncoder;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerForgotPassword(CoreEvent $event)
    {
        if ($event->getSuccess()) {

            $url = $this->getRouter()->generate('customer_update_password', [
                'id' => $event->getEntity()->getId(),
                'hash' => $event->getEntity()->getConfirmHash(),
            ]);

            $tplData = $event->getEntity()->getData();
            $tplData['url'] = $url;

            $tpl = 'Email:customer_update_password.html.twig';

            $body = $this->getThemeService()->renderView('email', $tpl, $tplData);

            $subject = 'Update Password';

            try {

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($this->getFromEmail())
                    ->setTo($event->getEntity()->getEmail())
                    ->setBody($body, 'text/html');

                $this->getMailer()->send($message);

            } catch(\Exception $e) {
                $event->addWarningMessage('An error occurred while sending email');
            }
        }
    }
}
