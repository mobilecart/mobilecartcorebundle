<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Cart;

/**
 * Class OrderNewReturn
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderNewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->getCartService()->getCartTotalService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCurrencyService();
    }

    /**
     * @param $paymentService
     * @return $this
     */
    public function setPaymentService($paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\PaymentService
     */
    public function getPaymentService()
    {
        return $this->paymentService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ShippingService
     */
    public function getShippingService()
    {
        return $this->getCartService()->getShippingService();
    }

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
    public function onOrderNewReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();

        $cart = new Cart();
        $totals = $this->getCartTotalService()
            ->setCart($cart)
            ->collectTotals()
            ->getTotals();

        $cart->setTotals($totals);

        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('cart', $cart);
        $event->setReturnData('entity', $entity);

        $event->setReturnData('template_sections', [
            'customer' => [
                'active' => 1,
                'label' => 'Customer',
                'section_id' => 'customer',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:customer_tabs.html.twig',
                'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:customer_tabs_js.html.twig',
                'customer_id' => 0,
                'form' => $event->getReturnData('form'),
                'form_elements' => [
                    'billing_firstname',
                    'billing_lastname',
                    'billing_street',
                    'billing_street2',
                    'billing_city',
                    'billing_region',
                    'billing_postcode',
                    'billing_country_id',
                    'billing_phone',
                ],
            ],
            'products' => [
                'label' => 'Products',
                'section_id' => 'products',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:product_grid_tabs.html.twig',
                'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:product_grid_tabs_js.html.twig',
                'product_ids' => [],
                'products' => [],
            ],
            'discounts' => [
                'label' => 'Discounts',
                'section_id' => 'discounts',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:discount_tabs.html.twig',
                'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:discount_tabs_js.html.twig',
                'discount_ids' => [],
            ],
            'totals' => [
                'label' => 'Totals',
                'section_id' => 'totals',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:totals.html.twig',
                'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:totals_js.html.twig',
                'totals' => $totals,
            ],
        ]);

        $event->setResponse($this->getThemeService()->render(
            'admin',
            'Order:new.html.twig',
            $event->getReturnData()
        ));
    }
}
