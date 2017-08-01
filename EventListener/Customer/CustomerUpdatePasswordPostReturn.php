<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerUpdatePasswordPostReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerUpdatePasswordPostReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    protected $router;

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
     * @param CoreEvent $event
     */
    public function onCustomerUpdatePasswordPostReturn(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $response = '';
        switch($format) {
            case 'json':

                if (!isset($returnData['success'])
                    || $returnData['success'] != 1
                ) {
                    $returnData['success'] = 0; // being explicit
                }

                $response = new JsonResponse($returnData);

                break;
            default:

                // todo : add message to session

                if (isset($returnData['success']) && $returnData['success'] == 1) {

                    $params = [];
                    $route = 'login_route';
                    $url = $this->getRouter()->generate($route, $params);
                    $response = new RedirectResponse($url);

                } else {

                    $params = [];
                    $route = 'customer_forgot_password';
                    $url = $this->getRouter()->generate($route, $params);
                    $response = new RedirectResponse($url);

                }

                break;
        }

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
