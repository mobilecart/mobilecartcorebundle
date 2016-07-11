<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;

use MobileCart\CoreBundle\Constants\CheckoutConstants;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CheckoutViewReturn
{
    protected $themeService;

    protected $cartSession;

    protected $paymentService;

    protected $entityService;

    protected $layout = 'frontend';

    protected $defaultTemplate = 'Checkout:index.html.twig';

    protected $event;

    protected $router;

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

    public function setDefaultTemplate($tpl)
    {
        $this->defaultTemplate = $tpl;
        return $this;
    }

    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    public function getTemplate()
    {
        return $this->getEvent()->getTemplate()
            ? $this->getEvent()->getTemplate()
            : $this->defaultTemplate;
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

    public function setCartSession($cartSession)
    {
        $this->cartSession = $cartSession;
        return $this;
    }

    public function getCartSession()
    {
        return $this->cartSession;
    }

    public function setPaymentService($paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

    public function getPaymentService()
    {
        return $this->paymentService;
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
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

    public function onCheckoutViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $cart = $this->getCartSession()
            ->collectShippingMethods()
            ->collectTotals()
            ->getCart();

        if (!$cart->hasItems()) {
            $response = new RedirectResponse($this->getRouter()->generate('cart_view', []));
            $event->setResponse($response);
            return;
        }

        $email = $cart->getCustomer()->getEmail();
        $returnData['cart'] = $cart;
        $returnData['email'] = $email;

        $returnData['country_regions'] = $this->getCartSession()
            ->getCountryRegions();

        $returnData['is_shipping_enabled'] = $this->getCartSession()
            ->getShippingService()
            ->getIsShippingEnabled();

        $returnData['totals_discounts_url'] = $this->getRouter()
            ->generate('cart_checkout_totals_discounts', []);

        $returnData['totals_discounts_section'] = CheckoutConstants::STEP_TOTALS_DISCOUNTS;

        if (!isset($returnData['javascripts'])) {
            $returnData['javascripts'] = [];
        }

        $response = $this->getThemeService()
            ->render($this->getLayout(), $this->getTemplate(), $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
