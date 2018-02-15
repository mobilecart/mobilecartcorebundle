<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutUpdateTotalsDiscounts
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateTotalsDiscounts
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
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
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutUpdateTotalsDiscounts(CoreEvent $event)
    {
        $isValid = true;
        $invalid = [];

        $nextSection = CheckoutConstants::STEP_PAYMENT_METHOD;

        $this->getCartService()->setSectionIsValid(CheckoutConstants::STEP_TOTALS_DISCOUNTS, $isValid);
        $this->getCartService()->saveCart();

        $event->setReturnData('success', $isValid);
        $event->setReturnData('cart', $this->getCartService()->getCart());
        $event->setReturnData('messages', $event->getMessages());
        $event->setReturnData('next_section', $nextSection);
        $event->setReturnData('invalid', $invalid);

        if ($isValid && strlen($nextSection)) {

            $event->setReturnData('redirect_url', $this->getRouter()->generate(
                'cart_checkout_section', [
                    'section' => $nextSection
                ]
            ));
        }

        $event->setResponse(new JsonResponse($event->getReturnData()));
    }
}
