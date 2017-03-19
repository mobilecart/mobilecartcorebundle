<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Form\CheckoutType;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutPaymentMethods
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

    /**
     * @param $formFactory
     * @return $this
     */
    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return mixed
     */
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
        if ($event->getSingleStep()
            && $event->getSingleStep() != CheckoutConstants::STEP_PAYMENT_METHODS) {

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

        // payment method request
        $methodRequest = $event->getCollectPaymentMethodRequest()
            ? $event->getCollectPaymentMethodRequest()
            : new CollectPaymentMethodRequest();

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

        $themeService = $this->getThemeService();
        $themeConfig = $themeService->getThemeConfig();
        $tplPath = $themeService->getTemplatePath($themeConfig->getFrontendTheme());

        // sections are also defined in other listeners
        //  in which case, we are combining sections here
        $sections = array_merge([
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

        $returnData['sections'] = $sections;
        $event->setReturnData($returnData);
    }
}
