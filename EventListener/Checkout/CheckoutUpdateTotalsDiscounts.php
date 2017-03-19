<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutUpdateTotalsDiscounts
{
    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var Event
     */
    protected $event;

    protected $router;

    /**
     * @param $checkoutSessionService
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
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

    /**
     * @param Event $event
     */
    public function onCheckoutUpdateTotalsDiscounts(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $isValid = 1;

        // todo : validation

        // todo : build error, warning messages

        // todo : set success flag

        // todo : update cart session

        $returnData['success'] = $isValid;
        $returnData['messages'] = [];
        $returnData['invalid'] = [];

        $cartService = $this->getCheckoutSessionService()->getCartSessionService()->getCartService();
        if ($isValid && !$cartService->getIsSpaEnabled()) {
            $returnData['redirect_url'] = $this->getRouter()->generate('cart_checkout_payment', []);
        }

        $this->getCheckoutSessionService()->setIsValidTotals($isValid);

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
