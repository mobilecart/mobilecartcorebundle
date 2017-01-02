<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
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
        $cart = $this->getCartSessionService()->getCart();

        $returnData['cart'] = $cart;
        $returnData['is_shipping_enabled'] = $this->getCartSessionService()
            ->getShippingService()
            ->getIsShippingEnabled();

        $returnData['is_multi_shipping_enabled'] = $this->getCartSessionService()
            ->getShippingService()
            ->getIsMultiShippingEnabled();

        $addressOptions = [];
        if ($returnData['is_multi_shipping_enabled']
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

        $returnData['addresses'] = $addressOptions;

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
