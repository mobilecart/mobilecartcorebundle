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

    /**
     * @param CoreEvent $event
     */
    public function onUpdateItemQtys(CoreEvent $event)
    {
        $isValid = false;
        $key = 'product_id';

        // handle/parse API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                $qtys = [];

                if (isset($apiRequest['qtys'])
                    && is_array($apiRequest['qtys'])
                ) {
                    foreach($apiRequest['qtys'] as $qtyData) {

                        $qtyData = get_object_vars($qtyData);

                        if (isset($qtyData['sku'])) {
                            $key = 'sku';
                        }

                        if (isset($qtyData['qty']) && isset($qtyData[$key])) {
                            $qtys[$qtyData[$key]] = $qtyData['qty'];
                        }
                    }
                }

                break;
            default:

                $qtys = $event->getRequest()->get('qty', []);

                break;
        }

        if (is_array($qtys) && $qtys) {

            $recollectShipping = [];

            foreach($qtys as $id => $qty) {
                if ($qty < 1) {

                    $innerEvent = new CoreEvent();
                    $innerEvent->setRequest($event->getRequest())
                        ->setIsMassUpdate(true)
                        ->setUser($event->getUser())
                        ->set($key, $id);

                    $this->getEventDispatcher()
                        ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $innerEvent);

                    if ($innerEvent->getSuccess()) {
                        $isValid = true;
                        if ($innerEvent->get('recollect_shipping', [])) {
                            foreach($innerEvent->get('recollect_shipping') as $anAddress) {
                                $recollectShipping[] = $anAddress;
                            }
                        }
                    }

                } else {

                    $innerEvent = new CoreEvent();
                    $innerEvent->setRequest($event->getRequest())
                        ->setIsMassUpdate(true)
                        ->setUser($event->getUser())
                        ->set($key, $id)
                        ->set('qty', $qty)
                        ->set('is_add', false);

                    $this->getEventDispatcher()
                        ->dispatch(CoreEvents::CART_ADD_PRODUCT, $innerEvent);

                    if ($innerEvent->getSuccess()) {
                        $isValid = true;
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

        $event->setSuccess($isValid);
        if ($event->getSuccess()) {
            $event->addSuccessMessage('Cart Updated !');
        }
    }
}
