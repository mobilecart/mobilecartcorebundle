<?php

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\Shipping\Rate;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class DB
 * @package MobileCart\CoreBundle\EventListener\Shipping
 *
 * This is a basic Shipping Rate collector
 *  This loads all Methods in the DB
 */
class DB extends Rate
{
    protected $entityService;

    protected $event;

    protected $isEnabled = 1;

    public function __construct()
    {
        parent::__construct();
    }

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

    public function setIsEnabled($yesNo = 1)
    {
        $this->isEnabled = $yesNo;
        return $this;
    }

    public function getIsEnabled()
    {
        return $this->isEnabled;
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
     * @return mixed
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * Get rates while filtering on criteria
     *
     * @param Event $event
     */
    public function onShippingRateCollect(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        // check criteria , load from db

        $methods = $this->getEntityService()
            ->findAll('shipping_method');

        if ($this->getIsEnabled()
            && $methods
        ) {
            foreach($methods as $method) {

                $rate = new Rate();
                $rate->addData($method->getData());
                $event->addRate($rate);
            }
        }

        $event->setReturnData($returnData);
    }
}
