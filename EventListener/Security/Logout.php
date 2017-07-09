<?php

namespace MobileCart\CoreBundle\EventListener\Security;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class Logout implements LogoutSuccessHandlerInterface
{
    protected $httpUtils;
    protected $targetUrl;

    /**
     * @var CartSessionService
     */
    protected $cartSessionService;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @param $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param HttpUtils $httpUtils
     * @param string    $targetUrl
     */
    public function __construct(HttpUtils $httpUtils, $targetUrl = '/')
    {
        $this->httpUtils = $httpUtils;

        $this->targetUrl = $targetUrl;
    }

    /**
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        $this->getCartSessionService()->initCart();

        $event = new CoreEvent();

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::LOGOUT_SUCCESS, $event);

        if ($event->getResponse()) {
            return $event->getResponse();
        }

        return $this->httpUtils->createRedirectResponse($request, $this->targetUrl);
    }
}
