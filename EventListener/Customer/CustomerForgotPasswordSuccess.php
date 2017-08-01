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
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $response = '';
        switch($format) {
            case 'json':

                $returnData = [
                    'success' => 1
                ];

                $response = new JsonResponse($returnData);

                break;
            default:

                $response = $this->getThemeService()
                    ->render('frontend', 'Customer:forgot_password_success.html.twig', $returnData);

                break;
        }

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
