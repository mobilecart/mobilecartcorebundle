<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class ViewReturn
{
    protected $cartSessionService;

    protected $themeService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    public function onCartViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $cart = $this->getCartSessionService()
            ->initCart()
            ->getCart();

        $returnData['cart'] = $cart;
        $returnData['is_shipping_enabled'] = $this->getCartSessionService()
            ->getShippingService()
            ->getIsShippingEnabled();

        $returnData['is_discount_enabled'] = $this->getCartSessionService()
            ->getDiscountService()
            ->getIsDiscountEnabled();

        $response = '';
        switch($format) {
            case 'json':

                $response = new JsonResponse($returnData);

                break;
            default:

                $response = $this->getThemeService()
                    ->render('frontend', 'Cart:index.html.twig', $returnData);

                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
