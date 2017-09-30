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
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
        return (bool) $this->getCheckoutSessionService()->getCartSessionService()->getCartService()->getIsSpaEnabled();
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutFormStart(CoreEvent $event)
    {
        // add js for accordion
        if ($this->getIsSpaEnabled()
            && !$event->getRequest()->get('ajax', '')
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
