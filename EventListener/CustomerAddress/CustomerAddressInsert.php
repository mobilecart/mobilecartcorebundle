<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerAddressInsert
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCustomerAddressInsert(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\CustomerAddress $entity */
        $entity = $event->getEntity();
        /** @var \MobileCart\CoreBundle\Entity\Customer $customer */
        $customer = $event->getCustomer();
        $entity->setCustomer($customer);
        $this->getEntityService()->persist($entity);

        if (!$this->getCartService()->getIsAdminUser()) {
            $this->getCartService()->setCustomerEntity($customer);
        }

        $event->addSuccessMessage('Customer Address Created!');
    }
}
