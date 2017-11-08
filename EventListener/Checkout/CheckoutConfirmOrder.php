<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutConfirmOrder
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutConfirmOrder
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var string
     */
    protected $layout = 'frontend';

    /**
     * @var string
     */
    protected $defaultTemplate = 'Checkout:confirm_order.html.twig';

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
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
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getCheckoutSessionService()->getCartService();
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
     * @param CoreEvent $event
     */
    public function onCheckoutConfirmOrder(CoreEvent $event)
    {
        $event->setReturnData('cart', $this->getCartService()->collectTotals()->getCart());

        $event->setReturnData('is_shipping_enabled', $this->getCartService()->getShippingService()->getIsShippingEnabled());

        $template = $event->getTemplate()
            ? $event->getTemplate()
            : $this->defaultTemplate;

        $event->setResponse($this->getThemeService()->render(
            $this->getLayout(),
            $template,
            $event->getReturnData()
        ));
    }
}
