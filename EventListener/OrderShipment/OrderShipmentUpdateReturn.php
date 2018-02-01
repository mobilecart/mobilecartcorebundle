<?php

namespace MobileCart\CoreBundle\EventListener\OrderShipment;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderShipmentUpdateReturn
 * @package MobileCart\CoreBundle\EventListener\OrderShipment
 */
class OrderShipmentUpdateReturn
{
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
     * @param CoreEvent $event
     */
    public function onOrderShipmentUpdateReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $url = $this->getRouter()->generate('cart_admin_order_shipment_edit', ['id' => $entity->getId()]);

        if ($event->hasFlashMessages()) {
            $event->flashMessages();
        }

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:
                $event->setResponse(new JsonResponse([
                    'success' => true,
                    'entity' => $entity->getData(),
                    'redirect_url' => $url,
                ]));
                break;
            default:
                $event->setResponse(new RedirectResponse($url));
                break;
        }
    }
}
