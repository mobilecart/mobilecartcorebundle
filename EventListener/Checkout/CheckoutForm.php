<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\CheckoutType;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutForm
{
    protected $entityService;

    protected $formFactory;

    protected $router;

    protected $paymentService;

    protected $checkoutSessionService;

    protected $themeService;

    protected $shippingService;

    protected $theme = 'frontend';

    protected $event;

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
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

    public function setPaymentService($paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

    public function getPaymentService()
    {
        return $this->paymentService;
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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
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

    public function getDisplayEmailInput()
    {
        return $this->getCheckoutSessionService()->getAllowGuestCheckout();
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

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

    public function onCheckoutForm(Event $event)
    {
        if ($event->getSingleStep()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        // sections are combined with other listeners/observer
        //  and later ordered
        $sections = isset($returnData['sections'])
            ? $returnData['sections']
            : [];

        $entity = $event->getEntity();

        $cartSession = $this->getCheckoutSessionService()
            ->getCartSessionService();

        $cart = $cartSession->getCart();

        $formType = new CheckoutType();
        $formType->setBillingAddressForm($event->getBillingAddressForm())
            ->setShippingAddressForm($event->getShippingAddressForm())
            ->setShippingMethodForm($event->getShippingMethodForm());

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
            'translation_domain' => 'checkout',
        ]);

        // todo : handle this in the billing address form event listener
        if (!$this->getDisplayEmailInput()) {
            $form->get('billing_address')->remove('email');
        }

        // payment
        $methodRequest = new CollectPaymentMethodRequest();
        $paymentMethods = $this->getPaymentService()
            ->collectPaymentMethods($methodRequest);

        if ($paymentMethods) {
            $methodCodes = [];

            // paymentMethod is an ArrayWrapper : code, label, form
            foreach($paymentMethods as $paymentMethod) {
                $methodCodes[] = $paymentMethod->getCode();
            }

            $cartSession->setPaymentMethodCodes($methodCodes);
        }

        $themeService = $event->getThemeService();
        $themeConfig = $themeService->getThemeConfig();
        $tplPath = $themeService->getTemplatePath($themeConfig->getFrontendTheme());

        // sections are also defined in other listeners
        //  in which case, we are combining sections here
        $sections = array_merge([
            CheckoutConstants::STEP_TOTALS_DISCOUNTS => [
                'order' => 40,
                'label' => 'Totals and Discounts',
                'template' => $tplPath . 'Checkout:totals_discounts.html.twig',
                'js_template' => $tplPath . 'Checkout:totals_discounts_js.html.twig',
                'post_url' => $this->getRouter()->generate('cart_checkout_update_discount', []),
            ],
            CheckoutConstants::STEP_PAYMENT_METHODS => [
                // this builds a form for each payment method
                'order' => 50,
                'label' => 'Payment',
                'template' => $tplPath . 'Checkout:payment_methods.html.twig',
                'js_template' => $tplPath . 'Checkout:payment_methods_js.html.twig',
                'payment_methods' => $paymentMethods,
                'post_url' => $this->getRouter()->generate('cart_checkout_update_payment', []),
                'final_step' => 1,
            ],
        ], $sections);

        // handle form sections
        $customer = $cart->getCustomer();
        foreach($sections as $section => $sectionData) {
            if (!isset($sections[$section]['fields'])) {
                continue;
            }
            foreach($sections[$section]['fields'] as $field) {
                if ($customerValue = $customer->get($field)) {
                    $form->get($section)->get($field)->setData($customerValue);
                }
            }
        }

        // get sort orders
        $sectionOrder = [];
        foreach($sections as $k => $v) {
            // set aside the order, for re-ordering
            $sectionOrder[$k] = $sections[$k]['order'];
        }

        // crude/quick sort and order the checkout steps
        $sectionOrder = array_flip($sectionOrder);
        ksort($sectionOrder);
        $sectionOrder = array_values($sectionOrder);

        // re-order the sections
        $checkoutSections = [];
        $x = 0;
        foreach($sectionOrder as $section) {
            $checkoutSections[$section] = $sections[$section];
            $x++;
            if (isset($sectionOrder[$x])) {
                $nextSection = $sectionOrder[$x];
                $checkoutSections[$section]['next_section_id'] = $nextSection;
            } else {
                $checkoutSections[$section]['final_step'] = 1;
                $checkoutSections[$section]['next_section_id'] = '';
            }
        }

        // build data for checkout widget
        $widgetFields = ['post_url', 'next_section_id', 'fields'];

        $returnData['widget_sections'] = [];
        foreach($checkoutSections as $k => $v) {
            $returnData['widget_sections'][$k]['section_id'] = $k;
            foreach($checkoutSections[$k] as $k2 => $v2) {
                if (in_array($k2, $widgetFields)) {
                    $returnData['widget_sections'][$k][$k2] = $v2;
                }
            }
        }

        // all sections ; ordered
        $returnData['sections'] = $checkoutSections;
        $returnData['form_name'] = $form->getName();

        $returnData['confirm_order_url'] = $this->getRouter()
            ->generate('cart_checkout_confirm_order', []);

        $returnData['submit_order_url'] = $this->getRouter()
            ->generate('cart_checkout_submit_order', []);

        $event->setForm($form)
            ->setReturnData($returnData);
    }
}
