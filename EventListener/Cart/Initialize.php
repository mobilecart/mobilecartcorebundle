<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class Initialize
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class Initialize extends BaseCartListener
{
    public function onCartInitialize(CoreEvent $event)
    {
        // allow a previous EventListener to set a custom value
        $hash = $event->get('hash', '')
            ? $event->get('hash', '')
            : sha1(microtime());

        $this->getCartService()->initCartEntity();
        $this->getCartService()->getCart()->setHashKey($hash);
        $this->getCartService()->getCartEntity()->setHashKey($hash);
        $this->getCartService()->saveCart();

        $event->setResponse(new JsonResponse([
            'hash' => $this->getCartService()->getCartEntity()->getHashKey(),
        ]));
    }
}
