<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\CheckoutConstants;
use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CheckoutPaymentMethodsViewReturn
{
    protected $themeService;

    protected $paymentService;

    protected $checkoutSessionService;

    protected $entityService;

    protected $layout = 'frontend';

    protected $defaultTemplate = 'Checkout:payment_methods_full.html.twig';

    protected $event;

    protected $router;

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

    public function setPaymentService($paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

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

    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function getCartSession()
    {
        return $this->getCheckoutSessionService()->getCartSessionService();
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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function onCheckoutPaymentMethodsViewReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();

        $cart = $this->getCartSession()
            ->collectShippingMethods()
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
