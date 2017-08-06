<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerAddressUpdate
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

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
     * @param CoreEvent $event
     */
    public function onCustomerAddressUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();

        $this->getEntityService()->persist($entity);

        if ($event->getSection() == CoreEvent::SECTION_FRONTEND) {

            // update session info
            $this->getCartSessionService()
                ->setCustomerEntity($event->getCustomer());
        }

        $event->addSuccessMessage('Customer Address Updated!');
    }
}
