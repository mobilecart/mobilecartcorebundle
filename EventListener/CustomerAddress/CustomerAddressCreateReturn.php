<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerAddressCreateReturn
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressCreateReturn
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
    public function onCustomerAddressCreateReturn(CoreEvent $event)
    {
        $url = $this->getRouter()->generate('customer_address_edit', [
            'id' => $event->getEntity()->getId()
        ]);

        $event->flashMessages();

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse([
                'success' => true,
                'entity' => $event->getEntity()->getData(),
                'redirect_url' => $url,
                'messages' => $event->getMessages(),
            ]));

        } else {

            $event->setResponse(new RedirectResponse($url));

        }
    }
}
