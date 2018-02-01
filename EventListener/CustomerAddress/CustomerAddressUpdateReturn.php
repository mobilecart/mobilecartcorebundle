<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerAddressUpdateReturn
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressUpdateReturn
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
    public function onCustomerAddressUpdateReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $url = $this->getRouter()->generate('customer_address_edit', ['id' => $entity->getId()]);
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
