<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class RemoveProducts
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class RemoveProducts extends BaseCartListener
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
    public function onCartRemoveProducts(CoreEvent $event)
    {
        // parse/convert API requests
        switch($event->getContentType()) {
            case 'application/json':

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array)json_decode($event->getRequest()->getContent());

                if (isset($apiRequest['cart_id'])) {
                    $keys = ['cart_id'];
                    foreach ($apiRequest as $key => $value) {
                        if (!in_array($key, $keys)) {
                            continue;
                        }

                        $event->getRequest()->request->set($key, $value);
                    }
                }
                break;
            default:

                break;
        }

        $this->initCart($event->getRequest());
        $recollectShipping = [];
        $success = false;
        if ($this->getCartService()->hasItems()) {
            foreach($this->getCartService()->getProductIds() as $productId) {

                $innerEvent = new CoreEvent();
                $innerEvent->setRequest($event->getRequest())
                    ->setIsMassUpdate(true)
                    ->setUser($event->getUser())
                    ->set('product_id', $productId);

                $this->getEventDispatcher()
                    ->dispatch(CoreEvents::CART_REMOVE_PRODUCT, $innerEvent);

                if ($innerEvent->getSuccess()) {
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
        $event->setSuccess($success);
    }
}
