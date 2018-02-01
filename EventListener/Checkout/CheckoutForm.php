<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutForm
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
     * @return bool
     */
    public function getIsSpaEnabled()
    {
        return (bool) $this->getCartService()->getIsSpaEnabled();
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutFormStart(CoreEvent $event)
    {
        $isAjax = $event->getRequest()
            ? $event->getRequest()->get('ajax', '') == 1
            : false;

        // add js for accordion
        if ($this->getIsSpaEnabled()
            && !$isAjax
        ) {
            $tplPath = $this->getThemeService()->getTemplatePath($this->getThemeService()->getThemeConfig()->getFrontendTheme());
            $javascripts = $event->getReturnData('javascripts', []);
            $javascripts['accordion'] = [
                'js_template' => $tplPath . 'Checkout:accordion_js.html.twig',
            ];
            $event->setReturnData('javascripts', $javascripts);
        }
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutFormEnd(CoreEvent $event)
    {
        if ($this->getIsSpaEnabled() && !$event->getResponse()) {

            $template = $event->get('template', '')
                ? $event->get('template', '')
                : 'Checkout:index.html.twig';

            $event->setResponse($this->getThemeService()->render(
                'frontend',
                $template,
                $event->getReturnData())
            );
        }
    }
}
