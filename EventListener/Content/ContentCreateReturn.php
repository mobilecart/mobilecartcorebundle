<?php

namespace MobileCart\CoreBundle\EventListener\Content;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ContentCreateReturn
 * @package MobileCart\CoreBundle\EventListener\Content
 */
class ContentCreateReturn
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
    public function onContentCreateReturn(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $url = $this->getRouter()->generate('cart_admin_content_edit', [
            'id' => $entity->getId()
        ]);

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
