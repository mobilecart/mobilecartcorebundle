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

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
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

        if ($event->getRequest()->getSession() && $event->getMessages()) {
            foreach($event->getMessages() as $code => $messages) {
                if (!$messages) {
                    continue;
                }
                foreach($messages as $message) {
                    $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                }
            }
        }

        switch($format) {
            case 'json':
                // security risk . be careful what we return here
                $event->setResponse(new JsonResponse([
                    'success' => $event->getReturnData('success') ? true : false
                ]));
                break;
            default:

                if ($event->getReturnData('success')) {

                    $event->addSuccessMessage('Password Successfully Updated!');
                    $event->setResponse(new RedirectResponse($this->getRouter()->generate('login_route', [])));
                } else {

                    $event->addErrorMessage('Invalid Request Submitted');
                    $event->setResponse(new RedirectResponse($this->getRouter()->generate('customer_forgot_password', [])));
                }
                break;
        }
    }
}
