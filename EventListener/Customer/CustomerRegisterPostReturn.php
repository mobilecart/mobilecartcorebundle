<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerRegisterPostReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerRegisterPostReturn
{
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
     * @param Event $event
     */
    public function onCustomerRegisterPostReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

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
