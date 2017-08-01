<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderUpdate
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

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
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderUpdate(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();
        $tracking = $request->get('tracking', []);

        $entity = $event->getEntity();
        $formData = $event->getFormData();

        $this->getEntityService()->persist($entity);

        if ($formData) {

            // update var values
            $this->getEntityService()
                ->persistVariants($entity, $formData);
        }

        if ($shipments = $entity->getShipments() && $tracking) {
            foreach($entity->getShipments() as $shipment) {
                if (isset($tracking[$shipment->getId()])
                    && $shipment->getTracking() != $tracking[$shipment->getId()]
                ) {
                    $shipment->setTracking($tracking[$shipment->getId()]);
                    $this->getEntityService()->persist($shipment);
                }
            }
        }


        if ($entity && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Order Updated!'
            );
        }

        $event->setReturnData($returnData);
    }
}
