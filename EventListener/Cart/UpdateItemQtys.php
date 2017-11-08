<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class UpdateItemQtys
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class UpdateItemQtys
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    public function onUpdateItemQtys(CoreEvent $event)
    {
        $request = $event->getRequest();
        $qtys = $request->get('qty', []);
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $event->set('format', $format);

        $success = false;
        if (is_array($qtys) && $qtys) {

            $recollectShipping = [];

            foreach($qtys as $productId => $qty) {
                if ($qty < 1) {

                    $innerEvent = new CoreEvent();
                    $innerEvent->setRequest($request)
                        ->setIsMassUpdate(true)
                        ->setUser($event->getUser())
                        ->set('product_id', $productId);

                    $this->getEventDispatcher()
                        ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $innerEvent);

                    if ($innerEvent->getReturnData('success')) {
                        $success = true;
                        if ($innerEvent->get('recollect_shipping', [])) {
                            foreach($innerEvent->get('recollect_shipping') as $anAddress) {
                                $recollectShipping[] = $anAddress;
                            }
                        }
                    }

                } else {

                    $innerEvent = new CoreEvent();
                    $innerEvent->setRequest($request)
                        ->setIsMassUpdate(true)
                        ->setUser($event->getUser())
                        ->set('product_id', $productId)
                        ->set('qty', $qty)
                        ->set('is_add', false);

                    $this->getEventDispatcher()
                        ->dispatch(CoreEvents::CART_ADD_PRODUCT, $innerEvent);

                    if ($innerEvent->getReturnData('success')) {
                        $success = true;
                        if ($innerEvent->get('recollect_shipping', [])) {
                            foreach($innerEvent->get('recollect_shipping') as $anAddress) {
                                $recollectShipping[] = $anAddress;
                            }
                        }
                    }
                }
            }

            $event->set('recollect_shipping', $recollectShipping);
        }

        $event->setReturnData('success', $success);
    }
}
