<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ViewReturn
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
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
    public function onCartViewReturn(CoreEvent $event)
    {
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $cart = $this->getCartService()->initCart()->getCart();

        $event->setReturnData('cart', $cart);
        $event->setReturnData('is_shipping_enabled', $this->getCartService()->getShippingService()->getIsShippingEnabled());
        $event->setReturnData('is_multi_shipping_enabled', $this->getCartService()->getShippingService()->getIsMultiShippingEnabled());

        $addressOptions = [];
        if ($cart->getCustomer()->getId()) {

            // get addresses from session
            $addresses = $this->getCartService()->getCustomerAddresses();
            if ($addresses) {
                foreach($addresses as $address) {
                    $addressOptions[] = [
                        'value' => $address->getId(),
                        'label' => $address->getLabel(),
                    ];
                }
            }

        } else {
            $addressOptions[] = [
                'value' => 'main',
                'label' => "Main Address",
            ];
        }

        $event->setReturnData('addresses', $addressOptions);
        $event->setReturnData('is_discount_enabled', (bool) $this->getCartService()->getDiscountService()->getIsDiscountEnabled());

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $event->setResponse($this->getThemeService()->render(
                    'frontend',
                    'Cart:index.html.twig',
                    $event->getReturnData()
                ));
                break;
        }
    }
}
