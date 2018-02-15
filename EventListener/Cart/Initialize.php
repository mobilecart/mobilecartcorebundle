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
        // enforce guest checkout
        if (!$this->getCartService()->getCheckoutFormService()->getAllowGuestCheckout()
            && !$event->getUser()
        ) {
            $event->setResponse(new JsonResponse([
                'success' => false,
                'messages' => [
                    'error' => [
                        'Guest Checkout is not allowed. Please login or register'
                    ]
                ]
            ], 401));
            return;
        }

        // allow a previous EventListener to set a custom value
        $hash = $event->get('hash', '')
            ? $event->get('hash', '')
            : sha1(microtime());

        $this->getCartService()->initCartEntity();
        $this->getCartService()->getCart()->setHashKey($hash);
        $this->getCartService()->getCartEntity()->setHashKey($hash);
        $this->getCartService()->saveCart();

        $event->setResponse(new JsonResponse($this->getCartService()->getCartEntity()->getHashKey()));
    }
}
