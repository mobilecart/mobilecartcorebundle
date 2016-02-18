<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Intl\Intl;

use MobileCart\CoreBundle\Shipping\RateRequest;
use MobileCart\CoreBundle\Form\CheckoutShippingMethodType;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutShippingMethodForm
{
    protected $event;

    protected $router;

    protected $checkoutSessionService;

    protected $shippingService;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
    {
        return $this->shippingService;
    }

    public function onCheckoutForm(Event $event)
    {
        if ($event->getSingleStep()
            && $event->getSingleStep() != CheckoutConstants::STEP_SHIPPING_METHOD) {

            return false;
        }

        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        // sections are combined with other listeners/observer
        //  and later ordered
        $sections = isset($returnData['sections'])
            ? $returnData['sections']
            : [];

        $themeService = $event->getThemeService();
        $themeConfig = $themeService->getThemeConfig();
        $tplPath = $themeService->getTemplatePath($themeConfig->getFrontendTheme());

        // shipping
        $rateRequest = new RateRequest();
        $shippingRates = $this->getShippingService()
            ->collectShippingRates($rateRequest);

        $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->setRates($shippingRates);

        $shippingMethods = [];
        if ($shippingRates) {
            foreach($shippingRates as $rate) {
                $shippingMethods[$rate->getCode()] = "{$rate->getCompany()} {$rate->getMethod()} - {$rate->getPrice()}";
            }
        }

        $shipmentMethod = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getShipmentMethod();

        $formType = new CheckoutShippingMethodType();
        $formType->setShippingMethods($shippingMethods)
            ->setDefaultValue($shipmentMethod);

        $sections = array_merge([
            CheckoutConstants::STEP_SHIPPING_METHOD => [
                'order' => 30,
                'label' => 'Shipping Method',
                'fields' => ['shipping_method'],
                'template' => $tplPath . 'Checkout:shipping_methods.html.twig',
                'js_template' => $tplPath . 'Checkout:shipping_methods_js.html.twig',
                'shipping_methods' => $shippingMethods,
                'post_url' => $this->getRouter()->generate('cart_checkout_update_shipping_method', []),
            ],
        ], $sections);

        $returnData['sections'] = $sections;

        $event->setReturnData($returnData)
            ->setShippingMethodForm($formType);

    }
}
