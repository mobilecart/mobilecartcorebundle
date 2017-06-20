<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CustomerRegister
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegister
{
    /**
     * @var Event
     */
    protected $event;

    protected $mailer;

    /**
     * @var string
     */
    protected $fromEmail = '';

    protected $router;

    protected $passwordEncoder;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @param $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
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
     * @param Event $event
     */
    public function onCustomerRegister(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();

        $existing = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER, [
            'email' => $formData['email'],
        ]);

        if ($existing) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'danger',
                'Customer Already Registered. Did you forget your password ?'
            );
            return;
        }

        $itemVarSet = $this->getEntityService()->findOneBy(EntityConstants::ITEM_VAR_SET, [
            'object_type' => EntityConstants::CUSTOMER,
        ]);

        $entity->setItemVarSet($itemVarSet);

        // encode password, handle hash
        if (isset($formData['password']['first']) && $formData['password']['first']) {
            $encoder = $this->getSecurityPasswordEncoder();
            $encoded = $encoder->encodePassword($entity, $formData['password']['first']);
            $entity->setHash($encoded);
        }

        $confirmHash = md5(microtime());
        $entity->setConfirmHash($confirmHash);
        $entity->setCreatedAt(new \DateTime('now'));

        $this->getEntityService()->persist($entity);

        if ($entity->getId()) {

            $recipient = $event->getRecipient()
                ? $event->getRecipient()
                : $entity->getEmail();

            $subject = $event->getSubject()
                ? $event->getSubject()
                : 'Account Registration';

            $event->addSuccessMessage("You are Registered");

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
                    ->setSubject($subject)
                    ->setFrom($this->getFromEmail())
                    ->setTo($recipient)
                    ->setBody($body, 'text/html');

                $this->getMailer()->send($message);

            } catch(\Exception $e) {
                // todo : handle error
            }
        }

        $event->setReturnData($returnData);
    }
}
