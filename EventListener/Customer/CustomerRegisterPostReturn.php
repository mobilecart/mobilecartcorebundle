<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CustomerRegisterPostReturn
{
    protected $request;

    protected $router;

    protected $entityService;

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function onCustomerRegisterPostReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $customer = $event->getEntity();

        $objectType = $event->getObjectType();

        $typeSections = [];

        $returnData['template_sections'] = $typeSections;

        $response = '';

        switch($format) {
            case 'json':
                $keep = ['id', 'email', 'first_name', 'last_name', 'name'];
                $customerData = $customer->getData();
                foreach($customerData as $k => $v) {
                    if (!in_array($k, $keep)) {
                        unset($customerData[$k]);
                    }
                }
                $customerData['success'] = 1;

                $response = new JsonResponse($customerData);
                break;
            default:

                if ($messages = $event->getMessages()) {
                    foreach($messages as $code => $message) {
                        $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                    }
                }

                $params = [];
                $route = 'customer_check_email';
                $url = $this->getRouter()->generate($route, $params);
                $response = new RedirectResponse($url);
                break;
        }

        $event->setResponse($response);
        $event->setReturnData($returnData);
    }
}
