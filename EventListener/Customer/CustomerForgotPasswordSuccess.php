<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
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
     * @var Event
     */
    protected $event;

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
     * @param Event $event
     */
    public function onCustomerForgotPasswordSuccess(Event $event)
    {
        $this->setEvent($event);
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
