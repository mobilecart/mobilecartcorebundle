<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderShipmentInsert
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
    public function onOrderShipmentInsert(CoreEvent $event)
    {
        $request = $event->getRequest();
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);
        $formData = $event->getFormData();

        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Shipment Created!'
            );
        }

        if (isset($formData['adjust_totals']) && $formData['adjust_totals']) {

            // load order entity

            // populate cart with json

            // update order.base_shipping_total, order.shipping_total

            // update order.base_total, order.total
        }
    }
}
