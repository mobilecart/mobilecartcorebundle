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
        $isValid = false;

        // todo : keep a count of invalid requests, logout/lockout user if excessive

        $invalidSections = $this->getCheckoutSessionService()->getInvalidSections();
        $event->setReturnData('invalid_sections', $invalidSections);
        if ($invalidSections) {

            $event->setResponse(new JsonResponse([
                'success' => false,
                'messages' => $event->getMessages(),
                'invalid_sections' => $invalidSections,
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
        $returnPaymentData = $event->getReturnData('payment_data', []);
        if ($returnPaymentData) {
            foreach($returnPaymentData as $k => $v) {
                $paymentData[$k] = $v;
            }
        }

        $cart = $this->getCheckoutSessionService()
            ->getCartService()
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
        if ($orderService->getErrors()) {
            foreach($orderService->getErrors() as $error) {
                $event->addErrorMessage($error);
            }
        }

        if ($isValid) {

            $event->setOrder($orderService->getOrder());

            $this->getCheckoutSessionService()
                ->getCartService()
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
                'invalid' => $invalidSections,
            ]));
        }
    }
}
