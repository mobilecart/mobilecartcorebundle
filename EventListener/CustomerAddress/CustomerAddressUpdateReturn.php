<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerAddressUpdateReturn
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressUpdateReturn
{
    protected $router;

    protected $session;

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

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Event $event
     */
    public function onCustomerAddressUpdateReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $response = '';
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        //$contentType = $request->headers->get('Accept');

        $params = ['id' => $entity->getId()];
        $route = 'customer_address_edit';
        $url = $this->getRouter()->generate($route, $params);

        switch($format) {
            case 'json':
                $returnData = [
                    'success' => 1,
                    'entity' => $entity->getData(),
                    'redirect_url' => $url,
                ];
                $response = new JsonResponse($returnData);
                break;
            default:

                if ($messages = $event->getMessages()) {
                    foreach($messages as $code => $message) {
                        $event->getRequest()->getSession()->getFlashBag()->add($code, $message);
                    }
                }

                $response = new RedirectResponse($url);
                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
