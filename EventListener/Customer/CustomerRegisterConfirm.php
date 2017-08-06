<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerRegisterConfirm
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterConfirm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    protected $mailer;

    /**
     * @var string
     */
    protected $fromEmail = '';

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
        $request = $event->getRequest();
        $id = $request->get('id', 0);
        $hash = $request->get('hash', '');
        $entity = $this->getEntityService()->find($event->getObjectType(), $id);

        // need extra security here to prevent hi-jacking
        //  current logic doesn't allow more than 15 brute force attempts
        //   or enable a locked account

        if ($entity
            && !$entity->getIsLocked()
            && $entity->getConfirmHash() == $hash) {

            $entity->setConfirmHash('')
                ->setIsEnabled(true)
                ->setIsLocked(false)
                ->setFailedLogins(0)
                ->setPasswordUpdatedAt(new \DateTime('now'));

            if (!$entity->getApiKey()) {
                $entity->setApiKey(sha1(microtime()));
            }

            $this->getEntityService()->persist($entity);

            $event->setReturnData('success', true);
            $event->setEntity($entity);

            $recipient = $event->getRecipient()
                ? $event->getRecipient()
                : $entity->getEmail();

            $subject = $event->getSubject()
                ? $event->getSubject()
                : 'Account Confirmed';

            $tplData = $entity->getData();

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
                // todo : handle error
            }

        } else {

            if ($entity) {

                // lock the account if we suspect brute force attempts

                $entity->setFailedLogins($entity->getFailedLogins() + 1);
                if ($entity->getFailedLogins() > 15
                    && !$entity->getIsLocked()
                ) {
                    $entity->setIsLocked(1);
                }

                $this->getEntityService()->persist($entity);
                $event->setEntity($entity);
            }

            $event->setReturnData('success', false);
        }
    }
}
