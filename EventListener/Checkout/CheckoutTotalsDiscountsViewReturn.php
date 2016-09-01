<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;

class CheckoutTotalsDiscountsViewReturn
{
    protected $themeService;

    protected $cartSession;

    protected $layout = 'frontend';

    protected $defaultTemplate = 'Checkout:totals_discounts.html.twig';

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

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function onCheckoutTotalsDiscounts(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();

        $returnData['cart'] = $this->getCartSession()
            ->collectShippingMethods()
            ->collectTotals()
            ->getCart();

        $returnData['is_shipping_enabled'] = $this->getCartSession()
            ->getShippingService()
            ->getIsShippingEnabled();

        if (!$this->getCartSession()->getCartService()->getIsSpaEnabled() && !$request->get('reload', 0)) {
            $this->setDefaultTemplate('Checkout:totals_discounts_full.html.twig');
            $returnData['section'] = $event->getSingleStep();
            $returnData['step_number'] = $event->getStepNumber();
        }

        $response = $this->getThemeService()
            ->render($this->getLayout(), $this->getTemplate(), $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
