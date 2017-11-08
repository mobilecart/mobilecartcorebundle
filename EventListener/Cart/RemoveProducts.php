<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;

/**
 * Class RemoveProducts
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class RemoveProducts
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
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
    public function onCartRemoveProducts(CoreEvent $event)
    {
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $event->set('format', $format);

        $cartService = $this->getCartService();

        $recollectShipping = [];
        $success = false;
        if ($cartService->hasItems()) {
            foreach($cartService->getProductIds() as $productId) {

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
            }
        }

        $event->set('recollect_shipping', $recollectShipping);
        $event->setReturnData('success', $success);
    }
}
