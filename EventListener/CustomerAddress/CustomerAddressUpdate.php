<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\EventDispatcher\Event;

class CustomerAddressUpdate
{
    protected $entityService;

    protected $event;

    protected $cartSessionService;

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

    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    public function onCustomerAddressUpdate(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        $this->getEntityService()->persist($entity);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            // update session info

            $this->getCartSessionService()
                ->setCustomerEntity($entity->getCustomer());
        }

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Customer Address Updated!'
            );
        }

        $event->setReturnData($returnData);
    }
}
