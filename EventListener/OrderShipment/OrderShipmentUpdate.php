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
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyServiceInterface
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCartTotalService()->getCurrencyService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderShipmentUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\OrderShipment $entity */
        $entity = $event->getEntity();
        $order = $entity->getOrder();

        $currency = $order->getCurrency();
        $baseCurrency = $this->getCurrencyService()->getBaseCurrency();

        $price = $currency == $baseCurrency
            ? $entity->getBasePrice()
            : $this->getCurrencyService()->convert($entity->getBasePrice(), $currency);

        $entity->setPrice($price);

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Shipment Updated !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Shipment');
            return;
        }

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

            if ($currency == $baseCurrency) {

                $order->setShippingTotal($baseShippingTotal)
                    ->setTotal($baseGrandTotal);

            } else {

                $order->setShippingTotal($this->getCurrencyService()->convert($baseShippingTotal, $currency))
                    ->setTotal($this->getCurrencyService()->convert($baseGrandTotal, $currency));
            }

            $order->setJson($this->getCartService()->getCart()->toJson());

            try {
                $this->getEntityService()->persist($order);
                $event->setSuccess(true);
                $event->addSuccessMessage('Order Updated !');
            } catch(\Exception $e) {
                $event->setSuccess(false);
                $event->addErrorMessage('An error occurred while saving Order');
            }
        }
    }
}
