<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerUpdatePasswordReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerUpdatePasswordReturn
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
    public function onCustomerUpdatePasswordReturn(CoreEvent $event)
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

                // be careful to not return _too much_ data
                $event->setResponse(new JsonResponse([
                    'success' => $event->getEntity() ? true : false
                ]));

                break;
            default:

                if ($event->getEntity()) {

                    $form = $event->getReturnData('form');
                    $form = $form->createView();
                    $event->setReturnData('form', $form);

                    $event->setResponse($this->getThemeService()->render(
                        'frontend',
                        'Customer:update_password.html.twig',
                        $event->getReturnData()
                    ));
                } else {

                    $event->setResponse($this->getThemeService()->render(
                        'frontend',
                        'Customer:update_password_notfound.html.twig',
                        $event->getReturnData()
                    ));
                }

                break;
        }
    }
}
