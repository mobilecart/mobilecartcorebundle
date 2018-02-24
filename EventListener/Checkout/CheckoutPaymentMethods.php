<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutPaymentMethods
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutPaymentMethods
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    protected $shippingService;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @param $orderService
     * @return $this
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
    }

    /**
     * @var string
     */
    protected $theme = 'frontend';

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\PaymentService
     * @return $this
     */
    public function setPaymentService(\MobileCart\CoreBundle\Service\PaymentService $paymentService)
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

    public function onCheckoutForm(CoreEvent $event)
    {
        if ($event->get('step_number', 0) > 0) {
            $event->set('step_number', $event->get('step_number') + 1);
        } else {
            $event->set('step_number', 1);
        }

        $javascripts = $event->getReturnData('javascripts', []);

        $tplPath = $this->getThemeService()->getThemeConfig()->getTemplatePath('frontend');
        $javascripts[] = [
            'js_template' => $tplPath . 'Checkout:payment_methods_js.html.twig',
        ];

        // payment method request
        $methodRequest = $event->getCollectPaymentMethodRequest()
            ? $event->getCollectPaymentMethodRequest()
            : new CollectPaymentMethodRequest();

        $paymentMethods = $this->getPaymentService()->collectPaymentMethods($methodRequest);
        if ($paymentMethods) {

            $methodCodes = [];

            // paymentMethod is an ArrayWrapper : code, label, form
            foreach($paymentMethods as $paymentMethod) {
                $methodCodes[] = $paymentMethod->getCode();
                if ($paymentMethod->get('javascripts')) {
                    foreach($paymentMethod->get('javascripts') as $javascript) {
                        $javascripts[] = $javascript;
                    }
                }
            }

            $this->getCartService()->setPaymentMethodCodes($methodCodes);
        }

        if (!$event->getRequest()
            || $event->getRequest()->getMethod() !== 'POST'
        ) {
            $event->setReturnData('javascripts', $javascripts);
        }

        $tplPath = $this->getThemeService()->getTemplatePath($this->getThemeService()->getThemeConfig()->getFrontendTheme());

        $sectionData = [
            'section' => CheckoutConstants::STEP_PAYMENT_METHOD,
            'order' => 50,
            'label' => 'Payment',
            //'payment_methods' => $paymentMethods,
            'post_url' => $this->getRouter()->generate('cart_checkout_update_section', [
                'section' => CheckoutConstants::STEP_PAYMENT_METHOD
            ]),
            'final_step' => true,
            'step_number' => $event->get('step_number'),
            'template' => $tplPath . 'Checkout:payment_methods.html.twig',
        ];

        if ($event->get('single_step', '')) {
            if ($event->get('single_step', '') == CheckoutConstants::STEP_PAYMENT_METHOD) {

                // if we're not submitting the form, then we render the template
                if ($event->getRequest()->getMethod() !== 'POST') {

                    $template = $event->getTemplate()
                        ? $event->getTemplate()
                        : 'Checkout:section_full.html.twig';

                    $sectionData['payment_methods'] = $paymentMethods;

                    $sectionData['javascripts'] = $javascripts;

                    $response = $this->getThemeService()->render('frontend', $template, $sectionData);
                    $event->setResponse($response);
                }
            }
        } else {

            $sectionData['payment_methods'] = $paymentMethods;

            $sections = $event->getReturnData('sections', []);
            $sections[CheckoutConstants::STEP_PAYMENT_METHOD] = $sectionData;
            $event->setReturnData('sections', $sections);
        }
    }
}
