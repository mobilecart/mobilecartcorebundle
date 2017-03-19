<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\CheckoutType;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    protected $formFactory;

    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    protected $shippingService;

    /**
     * @var string
     */
    protected $theme = 'frontend';

    /**
     * @var Event
     */
    protected $event;

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
     * @param $checkoutSession
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
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
     * @return bool
     */
    public function getDisplayEmailInput()
    {
        return $this->getCheckoutSessionService()->getAllowGuestCheckout();
    }

    /**
     * @param $theme
     * @return $this
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

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
     * @param Event $event
     * @return bool
     */
    public function onCheckoutForm(Event $event)
    {
        if ($event->getSingleStep()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $event->getReturnData();

        // sections are combined with other listeners/observer
        //  and later ordered
        $sections = isset($returnData['sections'])
            ? $returnData['sections']
            : [];

        $entity = $event->getEntity();

        $cartSession = $this->getCheckoutSessionService()
            ->getCartSessionService();

        $cart = $cartSession->getCart();
        $customer = $cart->getCustomer();

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

        $themeService = $this->getThemeService();
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
        ], $sections);

        // handle form sections

        foreach($sections as $section => $sectionData) {
            if (!isset($sections[$section]['fields'])) {
                continue;
            }
            foreach($sections[$section]['fields'] as $field) {

                $customerValue = $customer->get($field);

                switch($field) {
                    case 'is_shipping_same':
                        // must be a new "feature" in Symfony? It won't take a '1' anymore?
                        $form->get($section)->get($field)->setData((bool) $customerValue);
                        break;
                    case 'billing_name':
                        if ($customer->getFirstName() && !$customer->getBillingName()) {
                            $form->get($section)->get($field)->setData("{$customer->getFirstName()} {$customer->getLastName()}");
                        } else {
                            $form->get($section)->get($field)->setData($customerValue);
                        }
                        break;
                    default:
                        if (!is_null($customerValue)) {
                            $form->get($section)->get($field)->setData($customerValue);
                        }
                        break;
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
