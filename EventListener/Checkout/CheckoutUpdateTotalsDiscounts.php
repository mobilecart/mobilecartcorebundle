<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutUpdateTotalsDiscounts
{
    /**
     * @var CheckoutSessionService
     */
    protected $checkoutSessionService;

    protected $entityService;

    protected $event;

    protected $router;

    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

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

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
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

    public function onCheckoutUpdateTotalsDiscounts(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

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
