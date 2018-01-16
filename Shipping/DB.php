<?php

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\Event\Shipping\FilterShippingRateEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class DB
 * @package MobileCart\CoreBundle\EventListener\Shipping
 *
 * This is a basic Shipping Rate collector
 *  This loads all Methods in the DB
 */
class DB
{
    /**
     * @var \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    protected $entityService;

    /**
     * @var bool
     */
    protected $isEnabled = true;

    /**
     * @param bool $isEnabled
     * @return $this
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = (bool) $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return (bool) $this->isEnabled;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\DoctrineEntityService $entityService
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\DoctrineEntityService $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * Get rates while filtering on criteria
     *
     * @param FilterShippingRateEvent $event
     */
    public function onShippingRateCollect(FilterShippingRateEvent $event)
    {
        if ($this->getIsEnabled()) {
            if ($methods = $this->getEntityService()->findAll(EntityConstants::SHIPPING_METHOD)) {

                /** @var \MobileCart\CoreBundle\Shipping\RateRequest $rateRequest */
                $rateRequest = $event->getRateRequest();

                foreach($methods as $method) {
                    $rate = new Rate();
                    $rate->addData($method->getData());
                    $rate->setProductIds($rateRequest->getProductIds());
                    $rate->setSkus($rateRequest->getSkus());
                    $rate->set('shipping_method_id', $method->getId());
                    $event->addRate($rate);
                }
            }
        }
    }
}
