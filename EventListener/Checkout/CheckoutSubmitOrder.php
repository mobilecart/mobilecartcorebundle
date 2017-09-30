<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutSubmitOrder
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutSubmitOrder
{
    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
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
     * @param $orderService
     * @return $this
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

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
     * @return bool
     */
    public function onCheckoutSubmitOrder(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $isValid = false;
        //$returnData['messages'] = []; // r[messages] = [msg, msg, msg]
        //$returnData['invalid_sections'] = []; // r[invalid_sections] = [a, b, c]
        //$returnData['invalid_sections'] = $this->getCheckoutSessionService()->getInvalidSections(); // r[invalid_sections] = [a, b, c]
        $returnData['invalid'] = []; // r[invalid][section][field] = [msg, msg, msg]

        // todo : keep a count of invalid requests, logout/lockout user if excessive

        $invalid = $this->getCheckoutSessionService()->getInvalidSections();
        $event->setReturnData('invalid_sections', $invalid);
        if ($invalid) {

            $event->setResponse(new JsonResponse([
                'success' => false,
                'messages' => $event->getMessages(),
                'invalid_sections' => $invalid,
                'invalid' => [],
            ]));

            $event->set('is_valid', false);

            return false; // return early, return value has no effect
        }

        $paymentMethodCode = $this->getCheckoutSessionService()
            ->getPaymentMethodCode();

        $paymentMethodService = $this->getCheckoutSessionService()
            ->findPaymentMethodServiceByCode($paymentMethodCode);

        $paymentData = $this->getCheckoutSessionService()
            ->getPaymentData();

        // merge data
        if (isset($returnData['payment_data']) && is_array($returnData['payment_data'])) {
            foreach($returnData['payment_data'] as $k => $v) {
                $paymentData[$k] = $v;
            }
        }

        $cart = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCart();

        $orderService = $this->getOrderService()
            ->setCart($cart)
            ->setPaymentMethodService($paymentMethodService)
            ->setPaymentData($paymentData)
            ->setUser($event->getUser());

        // create order, orderItems, orderShipments, orderInvoice
        //  and payment, if necessary

        try {
            $orderService->submitCart();
            $isValid = true;
        } catch(\Exception $e) {
            $event->addErrorMessage("An exception occurred while placing the order");
        }

        $event->set('is_valid', $isValid);

        if ($isValid) {

            $event->setOrder($orderService->getOrder());

            $this->getCheckoutSessionService()
                ->getCartSessionService()
                ->removeItems()
                ->getSession()
                ->set('order_id', $orderService->getOrder()->getId());

            $event->setResponse(new JsonResponse([
                'success' => true,
                'redirect_url' => $this->getRouter()->generate('cart_checkout_success', []),
                'messages' => $event->getMessages(),
            ]));

        } else {

            $event->setResponse(new JsonResponse([
                'success' => false,
                'messages' => $event->getMessages(),
                'invalid' => $invalid,
            ]));
        }
    }
}
