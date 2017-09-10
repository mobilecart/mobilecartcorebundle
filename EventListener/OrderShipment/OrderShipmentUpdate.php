<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Shipment;

/**
 * Class OrderShipmentUpdate
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentUpdate
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
    public function onOrderShipmentUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $order = $entity->getOrder();
        $baseCurrency = $this->getCartService()->getCartTotalService()->getCurrencyService()->getBaseCurrency();
        if ($order->getCurrency() == $baseCurrency) {
            $entity->setPrice($entity->getBasePrice());
        } else {
            // todo : currency
        }

        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Shipment Updated!');

        $formData = $event->getFormData();
        if (isset($formData['adjust_totals']) && $formData['adjust_totals']) {

            // populate cart with json
            $this->getCartService()->initCartJson($order->getJson());

            $shipments = $order->getShipments();
            $this->getCartService()->removeShipments();

            foreach($shipments as $aEntity) {
                // create cart shipment from entity
                $shipment = new Shipment();
                $shipment->fromArray($aEntity->getData());
                $this->getCartService()->addShipment($shipment);
            }

            $this->getCartService()->collectTotals();

            $baseGrandTotal = $this->getCartService()
                ->getTotal(\MobileCart\CoreBundle\EventListener\Cart\GrandTotal::KEY)
                ->getValue();

            $baseShippingTotal = $this->getCartService()
                ->getTotal(\MobileCart\CoreBundle\EventListener\Cart\ShipmentTotal::KEY)
                ->getValue();

            $order->setBaseTotal($baseGrandTotal)
                ->setBaseShippingTotal($baseShippingTotal);

            if ($order->getCurrency() == $baseCurrency) {

                $order->setShippingTotal($baseShippingTotal)
                    ->setTotal($baseGrandTotal);

            } else {
                // todo : currency
            }

            $order->setJson($this->getCartService()->getCart()->toJson());
            $this->getEntityService()->persist($order);
        }
    }
}
