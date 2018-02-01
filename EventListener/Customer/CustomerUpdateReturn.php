<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerUpdateReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerUpdateReturn
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
    public function onCustomerUpdateReturn(CoreEvent $event)
    {
        $url = $this->getRouter()->generate('cart_admin_customer_edit', ['id' => $event->getEntity()->getId()]);
        $event->flashMessages();

        if ($event->isJsonResponse()) {
            $event->setResponse(new JsonResponse([
                'success' => true,
                'redirect_url' => $url,
                'messages' => $event->getMessages(),
            ]));
        } else {
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
