<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ViewReturn
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ViewReturn extends BaseCartListener
{
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
     * @param CoreEvent $event
     */
    public function onCartViewReturn(CoreEvent $event)
    {
        $this->initCart($event->getRequest());

        $addressOptions = [];
        if ($this->getCartService()->getCart()->getCustomer()->getId()) {

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

        $event->setSuccess(true);
        $event->setReturnData('cart', $this->getCartService()->getCart());
        $event->setReturnData('addresses', $addressOptions); // todo : remove this and use addresses in cart.customer
        $event->setReturnData('is_shipping_enabled', (bool) $this->getCartService()->getShippingService()->getIsShippingEnabled());
        $event->setReturnData('is_multi_shipping_enabled', (bool) $this->getCartService()->getShippingService()->getIsMultiShippingEnabled());
        $event->setReturnData('is_discount_enabled', (bool) $this->getCartService()->getDiscountService()->getIsDiscountEnabled());

        if ($event->isJsonResponse()) {
            $event->setResponse(new JsonResponse($event->getReturnData()));
        } else {
            $event->setResponse($this->getThemeService()->render(
                'frontend',
                'Cart:index.html.twig',
                $event->getReturnData()
            ));
        }
    }
}
