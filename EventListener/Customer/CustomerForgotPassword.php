<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerForgotPassword
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerForgotPassword
{
    protected $securityPasswordEncoder;

    protected $mailer;

    protected $router;

    /**
     * @var string
     */
    protected $fromEmail;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerForgotPassword(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

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

            $route = 'customer_update_password';

            $urlData = [
                'id' => $entity->getId(),
                'hash' => $confirmHash,
            ];

            $url = $this->getRouter()->generate($route, $urlData);

            $tplData = array_merge($entity->getData(), [
                'url' => $url,
            ]);

            $tpl = 'Email:customer_update_password.html.twig';

            $body = $this->getThemeService()->renderView('email', $tpl, $tplData);

            $subject = 'Update Password';

            try {

                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
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
