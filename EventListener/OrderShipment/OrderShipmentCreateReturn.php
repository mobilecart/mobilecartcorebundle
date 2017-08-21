<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class OrderShipmentCreateReturn
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentCreateReturn
{
    protected $router;

    protected $session;

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
     * @param CoreEvent $event
     */
    public function onOrderShipmentCreateReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $params = ['id' => $entity->getId()];
        $route = 'cart_admin_order_edit';
        $url = $this->getRouter()->generate($route, $params);

        switch($format) {
            case 'json':

                $event->setResponse(new JsonResponse([
                    'success' => true,
                    'entity' => $entity->getData(),
                    'redirect_url' => $url,
                ]));
                break;
            default:

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

                $event->setResponse(new RedirectResponse($url));
                break;
        }
    }
}
