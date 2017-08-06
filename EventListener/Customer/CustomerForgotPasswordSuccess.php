<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerForgotPasswordSuccess
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerForgotPasswordSuccess
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
    public function onCustomerForgotPasswordSuccess(CoreEvent $event)
    {
        $event->setResponse($this->getThemeService()->render(
            'frontend',
            'Customer:forgot_password_success.html.twig',
            $event->getReturnData()
        ));
    }
}
