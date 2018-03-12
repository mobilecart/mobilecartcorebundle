<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Cart;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;

/**
 * Class OrderEditReturn
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderEditReturn
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
    public function onOrderEditReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();

        $cart = new Cart();
        $cart->importJson($entity->getJson());

        // Totals _should_ be saved with cart, but they can be collected also
        $totals = $cart->getTotals();

        // gather discount IDs
        $discounts = $cart->getDiscounts();
        $discountIds = [];
        if ($discounts) {
            foreach($discounts as $discount) {
                $discountIds[] = $discount->getId();
            }
        }

        // gather shipping method codes
        $shipments = $entity->getShipments();
        $shippingMethods = $cart->getShippingMethods();
        $methodCodes = [];
        if ($shipments) {
            foreach($shipments as $shipment) {
                $methodCodes[] = $shipment->getCompany() . '-' . $shipment->getMethod();
            }
        }

        // gather product IDs
        $orderProductIds = [];
        $orderItems = $entity->getItems();
        if ($orderItems) {
            foreach($orderItems as $orderItem) {
                $orderProductIds[] = $orderItem->getProductId();
            }
        }

        $history = $this->getEntityService()->findBy(EntityConstants::ORDER_HISTORY, [
            'order' => $entity->getId(),
        ], [
            'created_at' => 'asc'
        ]);

        $event->setReturnData('entity', $entity);
        $event->setReturnData('form', $event->getForm()->createView());
        $event->setReturnData('cart', $cart);
        $event->setReturnData('discounts', $discounts);

        // todo : populate with customer info
        $methodRequest = new CollectPaymentMethodRequest();

        $event->setReturnData('template_sections', [
            'customer' => [
                'active' => 1,
                'label' => 'Customer',
                'section_id' => 'customer',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:customer_tabs.html.twig',
                'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:customer_tabs_js.html.twig',
                //'customer_id' => $customerId,
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
                'product_ids' => $orderProductIds,
                'products' => $orderItems,
            ],
            'shipping' => [
                'label' => 'Shipping',
                'section_id' => 'shipping',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:shipping_tabs.html.twig',
                //'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:shipping_tabs_js.html.twig',
                'shipping_methods' => $shippingMethods,
                'shipments' => $shipments,
                'method_codes' => $methodCodes,
            ],
            'discounts' => [
                'label' => 'Discounts',
                'section_id' => 'discounts',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:discount_tabs.html.twig',
                'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:discount_tabs_js.html.twig',
                'discount_ids' => $discountIds,
            ],
            'totals' => [
                'label' => 'Totals',
                'section_id' => 'totals',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:totals.html.twig',
                'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:totals_js.html.twig',
                'totals' => $totals,
            ],
            'payment' => [
                'label' => 'Payments',
                'section_id' => 'payments',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:payment_tabs.html.twig',
                //'js_template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:payment_tabs_js.html.twig',
                'payment_methods' => $this->getPaymentService()->collectPaymentMethods($methodRequest),
                'payments' => $entity->getPayments(),
            ],
            'history' => [
                'label' => 'History',
                'section_id' => 'history',
                'template' => $this->getThemeService()->getAdminTemplatePath() . 'Order/Edit:history.html.twig',
                'history' => $history,
            ],
        ]);

        $event->setResponse($this->getThemeService()->renderAdmin(
            'Order:edit.html.twig',
            $event->getReturnData()
        ));
    }
}
