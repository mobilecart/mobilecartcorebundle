<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Cart;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use MobileCart\CoreBundle\Shipping\RateRequest;

/**
 * Class OrderNewReturn
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderNewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    protected $shippingService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartTotalService
     */
    protected $cartTotalService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
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
     * @param $shippingService
     * @return $this
     */
    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ShippingService
     */
    public function getShippingService()
    {
        return $this->shippingService;
    }

    /**
     * @param $cartTotalService
     * @return $this
     */
    public function setCartTotalService($cartTotalService)
    {
        $this->cartTotalService = $cartTotalService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->cartTotalService;
    }

    /**
     * @param Event $event
     */
    public function onOrderNewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $order = $event->getEntity();

        $returnData['order'] = $order;

        $cart = new Cart();

        $totals = $this->getCartTotalService()
            ->setCart($cart)
            ->collectTotals()
            ->getTotals();

        $cart->setTotals($totals);

        // todo : handle customer creation
        $customerId = 0;

        // Totals _should_ be saved with cart, but they can be collected also

        $rateRequest = new RateRequest();
        $rateRequest->fromArray([
            'to_array'    => 0,
            'include_all' => 0,
            'postcode'    => '',
            'country_id'  => '',
            'region'      => '',
        ]);

        $shippingMethods = $this->getShippingService()
            ->collectShippingRates($rateRequest);

        $discountIds = [];
        $methodCodes = [];
        $payments = [];
        $shipments = [];
        $orderItems = [];
        $orderProductIds = [];

        $form = $returnData['form'];
        $returnData['form'] = $form->createView();

        // todo : populate with customer info
        $methodRequest = new CollectPaymentMethodRequest();

        $returnData['template_sections'] = [
            'customer' => [
                'active' => 1,
                'label' => 'Customer',
                'section_id' => 'customer',
                'template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:customer_tabs.html.twig',
                'js_template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:customer_tabs_js.html.twig',
                'customer_id' => $customerId,
                'form' => $returnData['form'],
                'form_elements' => [
                    'billing_name',
                    'billing_phone',
                    'billing_street',
                    'billing_street2',
                    'billing_city',
                    'billing_region',
                    'billing_postcode',
                    'billing_country_id',
                ],
            ],
            'products' => [
                'label' => 'Products',
                'section_id' => 'products',
                'template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:product_grid_tabs.html.twig',
                'js_template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:product_grid_tabs_js.html.twig',
                'product_ids' => $orderProductIds,
                'products' => $orderItems,
            ],
            'shipping' => [
                'label' => 'Shipping',
                'section_id' => 'shipping',
                'template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:shipping_tabs.html.twig',
                //'js_template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:shipping_tabs_js.html.twig',
                'shipping_methods' => $shippingMethods,
                'shipments' => $shipments,
                'method_codes' => $methodCodes,
            ],
            'discounts' => [
                'label' => 'Discounts',
                'section_id' => 'discounts',
                'template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:discount_tabs.html.twig',
                'js_template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:discount_tabs_js.html.twig',
                'discount_ids' => $discountIds,
            ],
            'totals' => [
                'label' => 'Totals',
                'section_id' => 'totals',
                'template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:totals.html.twig',
                'js_template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:totals_js.html.twig',
                'totals' => $totals,
            ],
            'payment' => [
                'label' => 'Payments',
                'section_id' => 'payments',
                'template'     => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:payment_tabs.html.twig',
                'js_template'  => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:payment_tabs_js.html.twig',
                'payment_methods' => $this->getPaymentService()->collectPaymentMethods($methodRequest),
                'payments' => $payments,
            ],
            'history' => [
                'label' => 'History',
                'section_id' => 'history',
                'template' => $this->getThemeService()->getTemplatePath('admin') . 'Order/Edit:history.html.twig',
            ],
        ];

        $returnData['cart'] = $cart;

        $returnData['entity'] = $order;

        $response = $this->getThemeService()
            ->render('admin', 'Order:new.html.twig', $returnData);

        $event->setResponse($response);
        $event->setReturnData($returnData);
    }
}
