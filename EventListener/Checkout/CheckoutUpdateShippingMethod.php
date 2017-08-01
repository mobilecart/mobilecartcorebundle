<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CheckoutUpdateShippingMethod
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutUpdateShippingMethod
{

    protected $formFactory;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

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
     * @param CoreEvent $event
     * @return bool
     */
    public function onCheckoutUpdateShippingMethod(CoreEvent $event)
    {
        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $formType = $event->getForm();

        // dummy object
        $entity = new \stdClass();
        $entity->shipping_method = '';

        $form = $this->getFormFactory()->create($formType, $entity, [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
            'translation_domain' => 'checkout',
        ]);

        $requestData = $request->request->all();
        $form->submit($requestData);
        $isValid = $form->isValid();

        if ($isValid) {

            $methodCode = $request->get('shipping_method');

            $cartSession = $this->getCheckoutSessionService()
                ->getCartSessionService();

            $cart = $cartSession->getCart();

            if ($cart->hasShippingMethodCode($methodCode)) {

                // todo : double-check this is the best way
                $shipment = $cart->getShippingMethod($cart->findShippingMethodIdx('code', $methodCode));

                $cartSession
                    ->removeShipments()
                    ->addShipment($shipment)
                    ->collectTotals();

                $cart = $cartSession->getCart();

                $returnData['cart'] = $cart;
            }
        }

        $this->getCheckoutSessionService()->setIsValidShippingMethod($isValid);

        $returnData['success'] = $isValid;
        $returnData['messages'] = [];
        $returnData['invalid'] = [];

        $response = new JsonResponse($returnData);

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
