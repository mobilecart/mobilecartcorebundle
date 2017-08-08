<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ViewReturn
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

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
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartViewReturn(CoreEvent $event)
    {
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $cart = $this->getCartSessionService()->getCart();

        $event->setReturnData('cart', $cart);
        $event->setReturnData('is_shipping_enabled', $this->getCartSessionService()->getShippingService()->getIsShippingEnabled());
        $event->setReturnData('is_multi_shipping_enabled', $this->getCartSessionService()->getShippingService()->getIsMultiShippingEnabled());

        $addressOptions = [];
        if ($event->getReturnData('is_multi_shipping_enabled')
            && $cart->getCustomer()->getId()
        ) {

            // get addresses from session
            $addresses = $this->getCartSessionService()->getCustomerAddresses();
            if ($addresses) {
                foreach($addresses as $address) {

                    if ($address instanceof \stdClass) {
                        $address = get_object_vars($address);
                    }

                    if (is_array($address)) {
                        $address = new ArrayWrapper($address);
                    }

                    if (strlen(trim($address->getStreet())) > 1) {
                        $label = "{$address->getStreet()} {$address->getCity()}, {$address->getRegion()}";
                        $value = $address->getId();
                        $addressOptions[] = [
                            'value' => $value,
                            'label' => $label,
                        ];
                    }
                }
            }

            if ($addressOptions && $event->getUser()) {
                if ($event->getUser()->getObjectTypeKey() == 'customer') {
                    $customer = $event->getUser();
                    if (strlen(trim($customer->getShippingStreet())) > 1) {
                        $label = "{$customer->getShippingStreet()} {$customer->getShippingCity()}, {$customer->getShippingRegion()}";
                        $addressOptions[] = [
                            'value' => 'main',
                            'label' => $label,
                        ];
                    }
                }
            }
        }

        $event->setReturnData('addresses', $addressOptions);
        $event->setReturnData('is_discount_enabled', $this->getCartSessionService()->getDiscountService()->getIsDiscountEnabled());

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
