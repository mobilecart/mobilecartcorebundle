<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\CheckoutConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    /**
     * @var Event
     */
    protected $event;

    protected $router;

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
     * @return string
     */
    public function getTemplate()
    {
        return $this->getEvent()->getTemplate()
            ? $this->getEvent()->getTemplate()
            : $this->defaultTemplate;
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
     * @param Event $event
     */
    public function onCheckoutPaymentMethodsViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $cart = $this->getCartSession()
            ->collectTotals()
            ->getCart();

        if (!$cart->hasItems()) {
            $response = new RedirectResponse($this->getRouter()->generate('cart_checkout', []));
            $event->setResponse($response);
            return;
        }

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

            $this->getCartSession()->setPaymentMethodCodes($methodCodes);
        }

        $returnData = array_merge($returnData,[
            // this builds a form for each payment method
            'order' => 50,
            'label' => 'Payment',
            'payment_methods' => $paymentMethods,
            'post_url' => $this->getRouter()->generate('cart_checkout_update_payment', []),
            'final_step' => 1,
            'section' => CheckoutConstants::STEP_PAYMENT_METHODS,
        ]);

        $response = $this->getThemeService()
            ->render($this->getLayout(), $this->getTemplate(), $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
