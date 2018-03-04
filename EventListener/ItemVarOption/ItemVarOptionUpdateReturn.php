<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarOption;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ItemVarOptionUpdateReturn
 * @package MobileCart\CoreBundle\EventListener\ItemVarOption
 */
class ItemVarOptionUpdateReturn
{
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
    public function onItemVarOptionUpdateReturn(CoreEvent $event)
    {
        $redirectUrl = $this->getRouter()->generate('cart_admin_item_var_option_edit', [
            'id' => $event->getEntity()->getId()
        ]);

        $event->flashMessages();

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:

                $event->setResponse(new JsonResponse([
                    'success' => $event->getSuccess(),
                    'entity' => $event->getEntity()->getData(),
                    'redirect_url' => $redirectUrl,
                    'messages' => $event->getMessages(),
                ]));

                break;
            default:

                $event->setResponse(new RedirectResponse($redirectUrl));

                break;
        }
    }
}
