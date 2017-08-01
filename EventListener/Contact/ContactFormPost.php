<?php

namespace MobileCart\CoreBundle\EventListener\Contact;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ContactFormPost
 * @package MobileCart\CoreBundle\EventListener\Contact
 */
class ContactFormPost
{
    /**
     * @var
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $emailTo;

    /**
     * @var string
     */
    protected $emailFrom;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    protected $router;

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
     * @param $emailTo
     * @return $this
     */
    public function setEmailTo($emailTo)
    {
        $this->emailTo = $emailTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailTo()
    {
        return $this->emailTo;
    }

    /**
     * @param $emailFrom
     * @return $this
     */
    public function setEmailFrom($emailFrom)
    {
        $this->emailFrom = $emailFrom;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailFrom()
    {
        return $this->emailFrom;
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

    public function onContactFormPost(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();
        $formData = $event->getFormData();

        $viewData = [
            'email' => $formData['email'],
            'name' => $formData['name'],
            'phone' => $formData['phone'],
            'message' => $formData['message'],
        ];

        // render template

        $body = $this->getThemeService()
            ->renderView('email', 'Email:contact_message.html.twig', $viewData);

        $subject = 'Contact Form Submission';
        $recipient = trim($this->getEmailTo());
        $fromEmail = trim($this->getEmailFrom());

        try {

            $msg = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($fromEmail)
                ->setTo($recipient)
                ->setBody($body, 'text/html');

            $this->getMailer()->send($msg);

        } catch(\Exception $e) {
            // todo : handle error
        }

        // redirect
        $route = 'cart_contact_thankyou';
        $url = $this->getRouter()->generate($route, []);
        $event->setResponse(new RedirectResponse($url));

        $event->setReturnData($returnData);
    }
}
