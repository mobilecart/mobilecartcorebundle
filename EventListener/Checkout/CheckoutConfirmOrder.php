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
     * @var string
     */
    protected $defaultTemplate = 'Checkout:confirm_order.html.twig';

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
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
     * @param CoreEvent $event
     */
    public function onCheckoutConfirmOrder(CoreEvent $event)
    {
        $event->setReturnData('cart', $this->getCartService()->getCart());

        $template = $event->getTemplate()
            ? $event->getTemplate()
            : $this->defaultTemplate;

        $event->setResponse($this->getThemeService()->renderFrontend(
            $template,
            $event->getReturnData()
        ));
    }
}
