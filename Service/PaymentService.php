<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\Event\Payment\FilterPaymentMethodCollectEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;

class PaymentService
{
    /**
     * @var mixed
     */
    protected $eventDispatcher;

    /**
     * @param $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param CollectPaymentMethodRequest $methodRequest
     * @return array
     */
    public function collectPaymentMethods(CollectPaymentMethodRequest $methodRequest)
    {
        // dispatch event
        $event = new FilterPaymentMethodCollectEvent();
        $event->setCollectPaymentMethodRequest($methodRequest);

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::PAYMENT_METHOD_COLLECT, $event);

        return $event->getMethods();
    }

    /**
     * @param $code
     * @return bool|PaymentMethodServiceInterface
     */
    public function findPaymentMethodServiceByCode($code)
    {
        $methodRequest = new CollectPaymentMethodRequest();
        // todo: populate methodRequest

        // dispatch event
        $event = new FilterPaymentMethodCollectEvent();
        $event->setCollectPaymentMethodRequest($methodRequest)
            ->setCode($code)
            ->setFindService(1);

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::PAYMENT_METHOD_COLLECT, $event);

        return $event->getService();
    }
}
