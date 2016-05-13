<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ShippingMethodCreateReturn
{
    protected $router;

    protected $session;

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

    public function onShippingMethodCreateReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $response = '';

        $entity = $event->getEntity();
        $request = $event->getRequest();
        $format = $request->get('format', '');
        //$contentType = $request->headers->get('Accept');

        $params = ['id' => $entity->getId()];
        $route = 'cart_admin_shipping_method_edit';
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
            //case 'xml':
            //
            //    break;
            default:

                if ($messages = $event->getMessages()) {
                    foreach($messages as $code => $message) {
                        $this->getSession()->getFlashBag()->add($code, $message);
                    }
                }

                $response = new RedirectResponse($url);
                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }

}
