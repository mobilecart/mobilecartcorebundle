<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;

class CustomerRegisterConfirm
{
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

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function onCustomerRegisterConfirm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

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
                ->setIsEnabled(1)
                ->setIsLocked(0)
                ->setFailedLogins(0)
                ->setPasswordUpdatedAt(new \DateTime('now'))
            ;

            if (!$entity->getApiKey()) {
                $entity->setApiKey(sha1(microtime()));
            }

            $this->getEntityService()->persist($entity);
            $event->setSuccess(1);
            $event->setEntity($entity);
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

            $event->setSuccess(0);
        }

        $event->setReturnData($returnData);
    }
}
