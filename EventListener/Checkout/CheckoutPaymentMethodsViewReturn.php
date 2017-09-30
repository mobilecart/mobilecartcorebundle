<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;

/**
 * Class CheckoutPaymentMethodsViewReturn
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutPaymentMethodsViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var string
     */
    protected $layout = 'frontend';

    /**
     * @var string
     */
    protected $defaultTemplate = 'Checkout:payment_methods_full.html.twig';

    protected $router;

    /**
     * @param $tpl
     * @return $this
     */
    public function setDefaultTemplate($tpl)
    {
        $this->defaultTemplate = $tpl;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\ThemeService $themeService
     * @return $this
     */
    public function setThemeService(\MobileCart\CoreBundle\Service\ThemeService $themeService)
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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $checkoutSessionService
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
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
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSession()
    {
        return $this->getCheckoutSessionService()->getCartSessionService();
    }

    /**
     * @param $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param $router
     * @return $this
     */
    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return bool
     */
    public function getIsSpaEnabled()
    {
        return (bool) $this->getCheckoutSessionService()->getCartSessionService()->getCartService()->getIsSpaEnabled();
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutPaymentMethodsViewReturn(CoreEvent $event)
    {
        if (!$this->getCartSession()->hasItems()) {
            $response = new RedirectResponse($this->getRouter()->generate('cart_checkout', []));
            $event->setResponse($response);
            return;
        }

        if ($event->get('step_number', 0) > 0) {
            $event->set('step_number', $event->get('step_number') + 1);
        } else {
            $event->set('step_number', 1);
        }

        $javascripts = $event->getReturnData('javascripts', []);

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

            $this->getCartSession()->setPaymentMethodCodes($methodCodes);
        }

        $tplPath = $this->getThemeService()->getTemplatePath($this->getThemeService()->getThemeConfig()->getFrontendTheme());

        $sectionData = [
            'section' => CheckoutConstants::STEP_PAYMENT_METHOD,
            'step_number' => $event->get('step_number'),
            'label' => 'Payment',
            'payment_methods' => $paymentMethods,
            'post_url' => $this->getRouter()->generate('cart_checkout_update_payment', []),
            'final_step' => true,
            'template' => $tplPath . 'Checkout:payment_methods.html.twig',
        ];

        if ($event->get('single_step', '')) {
            if ($event->get('single_step', '') == CheckoutConstants::STEP_PAYMENT_METHOD) {

                $template = $event->getTemplate()
                    ? $event->getTemplate()
                    : 'Checkout:section_full.html.twig';

                $javascripts[] = [
                    'js_template' => $tplPath . 'Checkout:payment_methods_js.html.twig',
                ];

                $sectionData['javascripts'] = $javascripts;

                $event->setResponse($this->getThemeService()->render('frontend', $template, $sectionData));
            }
        } else {

            if (!$event->getRequest()->get('ajax', '')) {
                $javascripts[] = [
                    'js_template' => $tplPath . 'Checkout:payment_methods_js.html.twig',
                    'data' => $sectionData,
                ];

                $event->setReturnData('javascripts', $javascripts);
            }
        }

        $sections = $event->getReturnData('sections', []);
        $sections[CheckoutConstants::STEP_PAYMENT_METHOD] = $sectionData;
        $event->setReturnData('sections', $sections);
    }
}
