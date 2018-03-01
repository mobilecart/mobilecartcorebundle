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
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
    public function onCustomerAddressUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\CustomerAddress $entity */
        $entity = $event->getEntity();

        /** @var \MobileCart\CoreBundle\Entity\Customer $customer */
        $customer = $event->getCustomer();

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Customer Address Updated !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Customer Address');
        }

        if ($event->getSuccess()) {
            if (!$this->getCartService()->getIsAdminUser()) {
                $this->getCartService()->setCustomerEntity($customer);
            }
        }
    }
}
