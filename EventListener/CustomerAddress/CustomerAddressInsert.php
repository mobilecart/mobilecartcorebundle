<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CustomerAddressInsert
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
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
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * @param Event $event
     */
    public function onCustomerAddressInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $request = $event->getRequest();
        $entity = $event->getEntity();
        $formData = $event->getFormData();

        $entity->setCustomer($event->getCustomer());

        $this->getEntityService()->persist($entity);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {
            // update session info

            $this->getCartSessionService()
                ->setCustomerEntity($event->getCustomer());
        }

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Customer Address Created!'
            );
        }

        $event->setReturnData($returnData);
    }
}
