<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class CheckoutUpdateShippingAddress
{
    protected $event;

    protected $formFactory;

    protected $checkoutSessionService;

    protected $router;

    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
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

    public function onCheckoutUpdateShippingAddress(Event $event)
    {
        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $formType = $event->getForm();
        $entity = $event->getEntity();

        $cartCustomer = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCustomer();

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
            'translation_domain' => 'checkout',
            'validation_groups' => 'shipping_address',
        ]);

        $requestData = $request->request->all();

        // todo : check is_shipping_same, copy values

        $form->submit($requestData);
        $isValid = $form->isValid();

        $messages = [];
        $invalid = [];

        if ($isValid) {

            //update customer data in cart session
            $formData = $form->getData();
            foreach($form->all() as $childKey => $child) {
                $cartCustomer->set($childKey, $formData->get($childKey));
            }

        } else {

            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    $invalid[$childKey] = [];
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }
        }

        $this->getCheckoutSessionService()->setIsValidShippingAddress($isValid);

        $returnData['success'] = $isValid;
        $returnData['messages'] = $messages;
        $returnData['invalid'] = $invalid;

        $cartService = $this->getCheckoutSessionService()->getCartSessionService()->getCartService();
        if ($isValid && !$cartService->getIsSpaEnabled()) {
            $returnData['redirect_url'] = $this->getRouter()->generate('cart_checkout_totals_discounts', []);
        }

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
