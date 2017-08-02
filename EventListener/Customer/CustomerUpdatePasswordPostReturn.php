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
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:

                if ($event->getReturnData('success')) {

                    $event->addSuccessMessage('Password Successfully Updated!');

                    $params = [];
                    $route = 'login_route';
                    $url = $this->getRouter()->generate($route, $params);
                    $event->setResponse(new RedirectResponse($url));

                } else {

                    $event->addErrorMessage('Invalid Request Submitted');

                    $params = [];
                    $route = 'customer_forgot_password';
                    $url = $this->getRouter()->generate($route, $params);
                    $event->setResponse(new RedirectResponse($url));

                }

                if ($messages = $event->getMessages()) {
                    foreach($messages as $code => $message) {
                        $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                    }
                }

                break;
        }
    }
}
