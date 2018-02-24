<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderUpdate
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
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
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->getCartService()->getCartTotalService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DiscountService
     */
    public function getDiscountService()
    {
        return $this->getCartService()->getDiscountService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCurrencyService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\Order $entity */
        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();
        $customerId = $request->get('customer_id', 0);

        if ($entity->get('customer_id') != $customerId) {
            $customer = $this->getEntityService()->find(EntityConstants::CUSTOMER, $customerId);
            if ($customer) {
                $entity->setCustomer($customer);
            }
        }

        $this->getEntityService()->persist($entity);

        if ($formData) {

            $this->getEntityService()
                ->persistVariants($entity, $formData);
        }

        $username = $event->getUser()
            ? $event->getUser()->getEmail()
            : $entity->getEmail();

        /** @var \MobileCart\CoreBundle\Entity\OrderHistory $history */
        $history = $this->getEntityService()->getInstance(EntityConstants::ORDER_HISTORY);
        $history->setCreatedAt(new \DateTime('now'))
            ->setOrder($entity)
            ->setUser($username)
            ->setMessage('Order Updated')
            ->setHistoryType(\MobileCart\CoreBundle\Entity\OrderHistory::TYPE_STATUS);

        // update tracking numbers on shipments, if necessary
        $request = $event->getRequest();
        $tracking = $request->get('tracking', []);

        $shipments = $entity->getShipments();
        if ($shipments && $tracking) {
            foreach($entity->getShipments() as $shipment) {
                if (isset($tracking[$shipment->getId()])
                    && $shipment->getTracking() != $tracking[$shipment->getId()]
                ) {
                    $shipment->setTracking($tracking[$shipment->getId()]);
                    $this->getEntityService()->persist($shipment);
                }
            }
        }

        $event->addSuccessMessage('Order Updated!');
    }
}
