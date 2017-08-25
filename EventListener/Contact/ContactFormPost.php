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

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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

    /**
     * @param $mailer
     * @return $this
     */
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param CoreEvent $event
     */
    public function onContactFormPost(CoreEvent $event)
    {
        $body = $this->getThemeService()
            ->renderView('email', 'Email:contact_message.html.twig', $event->getFormData());

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
            $event->addErrorMessage('Error sending email');
        }

        if ($event->getRequest()->getSession() && $event->getMessages()) {
            foreach($event->getMessages() as $code => $messages) {
                if (!$messages) {
                    continue;
                }
                foreach($messages as $message) {
                    $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                }
            }
        }

        $event->setResponse(new RedirectResponse($this->getRouter()->generate('cart_contact_thankyou', [])));
    }
}
