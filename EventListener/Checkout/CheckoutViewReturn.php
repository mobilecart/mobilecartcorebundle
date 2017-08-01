<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\CheckoutConstants;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CheckoutViewReturn
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSession;

    /**
     * @var \MobileCart\CoreBundle\Service\PaymentService
     */
    protected $paymentService;

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
    protected $defaultTemplate = 'Checkout:index.html.twig';

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
     * @param $cartSession
     * @return $this
     */
    public function setCartSession($cartSession)
    {
        $this->cartSession = $cartSession;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSession()
    {
        return $this->cartSession;
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
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
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
     * @param CoreEvent $event
     */
    public function onCheckoutViewReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $cart = $this->getCartSession()
            //->collectShippingMethods() // avoid collecting shipping methods unless the cart changes or the shipping address changes
            ->collectTotals()
            ->getCart();

        if (!$cart->hasItems()) {
            $response = new RedirectResponse($this->getRouter()->generate('cart_view', []));
            $event->setResponse($response);
            return;
        }

        $email = $cart->getCustomer()->getEmail();
        $returnData['cart'] = $cart;
        $returnData['email'] = $email;

        $returnData['country_regions'] = $this->getCartSession()
            ->getCountryRegions();

        if ($this->getCartSession()->getCartService()->getIsSpaEnabled()) {

            $returnData['is_shipping_enabled'] = $this->getCartSession()
                ->getShippingService()
                ->getIsShippingEnabled();

            $returnData['is_multi_shipping_enabled'] = $this->getCartSession()
                ->getShippingService()
                ->getIsMultiShippingEnabled();

            $returnData['totals_discounts_url'] = $this->getRouter()
                ->generate('cart_checkout_totals_discounts', []);

            $returnData['totals_discounts_section'] = CheckoutConstants::STEP_TOTALS_DISCOUNTS;

        } else {

            $returnData['section'] = $event->getSingleStep();
            $returnData['step_number'] = $event->getStepNumber();
            $this->defaultTemplate = 'Checkout:address.html.twig';
        }

        if (!isset($returnData['javascripts'])) {
            $returnData['javascripts'] = [];
        }

        if (!$event->getDisableRender()) {

            $template = $event->getTemplate()
                ? $event->getTemplate()
                : $this->defaultTemplate;

            $response = $this->getThemeService()->render($this->getLayout(), $template, $returnData);
            $event->setResponse($response);
        }

        $event->setReturnData($returnData);
    }
}
