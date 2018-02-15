<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

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
     * @var \MobileCart\CoreBundle\Service\CheckoutFormService
     */
    protected $checkoutFormService;

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
     * @param \MobileCart\CoreBundle\Service\CheckoutFormService $checkoutFormService
     * @return $this
     */
    public function setCheckoutFormService(\MobileCart\CoreBundle\Service\CheckoutFormService $checkoutFormService)
    {
        $this->checkoutFormService = $checkoutFormService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CheckoutFormService
     */
    public function getCheckoutFormService()
    {
        return $this->checkoutFormService;
    }

    /**
     * @return bool
     */
    public function getIsSinglePage()
    {
        return $this->getCheckoutFormService()->getIsSinglePage();
    }

    /**
     * This runs before the other steps are collected,
     *  and is for initializing the single page template javascripts
     *
     * @param CoreEvent $event
     */
    public function onCheckoutFormStart(CoreEvent $event)
    {
        // add js for accordion
        if ($this->getIsSinglePage()
            && !$event->get('single_step', '')
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
     * This runs after the other steps are collected,
     *  and is for rendering the single page template
     *
     * @param CoreEvent $event
     */
    public function onCheckoutFormEnd(CoreEvent $event)
    {
        if ($event->getReturnData('sections', [])) {

            // sort and set next_section , etc
            $event->setReturnData(
                'sections',
                $this->getCheckoutFormService()->sortFormSections($event->getReturnData('sections', []))
            );
        }

        if ($this->getIsSinglePage()
            && !$event->getResponse()
            && !$event->get('single_step', '')
        ) {

            $template = $event->get('template', '')
                ? $event->get('template', '')
                : 'Checkout:index.html.twig';

            $event->setResponse($this->getThemeService()->render(
                'frontend',
                $template,
                $event->getReturnData()
            ));
        }
    }
}
