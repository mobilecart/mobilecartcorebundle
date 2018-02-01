<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderCreateReturn
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderCreateReturn
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
    public function onOrderCreateReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $url = $this->getRouter()->generate('cart_admin_order_edit', ['id' => $entity->getId()]);

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
