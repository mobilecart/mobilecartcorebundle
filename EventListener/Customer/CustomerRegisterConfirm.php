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

        if ($entity && $entity->getConfirmHash() == $hash) {

            $apiKey = md5(microtime());

            $entity->setConfirmHash('')
                ->setIsEnabled(1)
                ->setApiKey($apiKey);

            // todo : update password_updated_at

            $this->getEntityService()->persist($entity);
            $event->setSuccess(1);
            $event->setEntity($entity);
        } else {
            $event->setSuccess(0);
        }

        $event->setReturnData($returnData);
    }
}
