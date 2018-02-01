<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
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

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
    public function onCustomerRegisterConfirm(CoreEvent $event)
    {
        $request = $event->getRequest();
        $id = $request->get('id', 0);
        $hash = $request->get('hash', '');

        /** @var \MobileCart\CoreBundle\Entity\Customer $entity */
        $entity = $this->getEntityService()->find(EntityConstants::CUSTOMER, $id);

        // need extra security here to prevent hi-jacking
        //  current logic doesn't allow more than 15 brute force attempts
        //   or enable a locked account

        if ($entity) {
            if (!$entity->getIsLocked()
                && $entity->getConfirmHash() == $hash
            ) {

                $entity->setConfirmHash('')
                    ->setIsEnabled(true)
                    ->setIsLocked(false)
                    ->setFailedLogins(0)
                    ->setPasswordUpdatedAt(new \DateTime('now'));

                if (!$entity->getApiKey()) {
                    $entity->setApiKey(sha1(microtime()));
                }

                try {
                    $this->getEntityService()->persist($entity);
                    $event->setSuccess(true);
                    $event->setEntity($entity);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving the Customer.');
                    $event->setSuccess(false);
                }
            } else {

                // lock the account if we suspect brute force attempts

                $event->setSuccess(false);
                $event->addWarningMessage('Invalid Request');

                $entity->setFailedLogins($entity->getFailedLogins() + 1);
                if ($entity->getFailedLogins() > 10 && !$entity->getIsLocked()) {
                    $entity->setIsLocked(1);
                    $event->addWarningMessage('The account has been temporarily locked');
                }

                try {
                    // update numer of failed logins, and lock, if necessary
                    $this->getEntityService()->persist($entity);
                    $event->setEntity($entity);
                } catch(\Exception $e) {
                    $event->addErrorMessage('An error occurred while saving the Customer.');
                }
            }
        } else {
            sleep(1); // slow things down, stop the bots
        }

        $event->setReturnData('template_sections', []);

        if ($event->getSuccess()) {

            if ($event->isJsonResponse()) {
                $event->setResponse(new JsonResponse([
                    'success' => true,
                ]));
            } else {

                $tpl = 'Customer:register_confirm_success.html.twig';
                $event->addReturnData($entity->getData());
                $event->setResponse($this->getThemeService()->render(
                    'frontend',
                    $tpl,
                    $event->getReturnData()
                ));
            }
        } else {
            if ($event->isJsonResponse()) {
                $event->setResponse(new JsonResponse([
                    'success' => false,
                ]));
            } else {

                $tpl = 'Customer:register_confirm_error.html.twig';
                $event->setResponse($this->getThemeService()->render(
                    'frontend',
                    $tpl,
                    $event->getReturnData()
                ));
            }
        }

        $event->flashMessages();
    }
}
