<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CustomerProfileReturn
{
    protected $request;

    protected $entityService;

    protected $themeService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function onCustomerProfileReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $customer = $event->getEntity();
        $objectType = $event->getObjectType();

        $request = $event->get('request');
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $response = '';
        switch($format) {
            case 'json':

                $returnData = [
                    'entity' => $customer->getData(),
                ];

                $response = new JsonResponse($returnData);
                break;
            default:

                $typeSections = [];
                $returnData['template_sections'] = $typeSections;

                if ($messages = $event->getMessages()) {
                    foreach($messages as $code => $message) {
                        $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                    }
                }

                $response = $this->getThemeService()
                    ->render('frontend', 'Customer:profile.html.twig', $returnData);

                break;
        }

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
