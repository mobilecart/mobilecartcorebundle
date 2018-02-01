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
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\PaymentService
     */
    public function getPaymentService()
    {
        return $this->getOrderService()->getPaymentService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getOrderService()->getEntityService();
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
        // todo : keep a count of invalid requests, logout/lockout user if excessive

        $invalidSections = $this->getCartService()->getInvalidSections();
        $event->setReturnData('invalid_sections', $invalidSections);
        if ($invalidSections) {

            $event->setResponse(new JsonResponse([
                'success' => false,
                'messages' => $event->getMessages(),
                'invalid_sections' => $invalidSections,
                'invalid' => [],
            ]));

            return false; // return early, return value has no effect
        }

        $paymentMethodService = $this->getPaymentService()
            ->findPaymentMethodServiceByCode($this->getCartService()->getPaymentMethodCode());

        $paymentData = $this->getCartService()->getPaymentData();

        // merge data
        $returnPaymentData = $event->getReturnData('payment_data', []);
        if ($returnPaymentData) {
            foreach($returnPaymentData as $k => $v) {
                $paymentData[$k] = $v;
            }
        }

        $orderService = $this->getOrderService()
            ->setCart($this->getCartService()->getCart())
            ->setPaymentMethodService($paymentMethodService)
            ->setPaymentData($paymentData)
            ->setUser($event->getUser());

        // create order, orderItems, orderShipments, orderInvoice
        //  and payment, if necessary

        $orderService->submitCart();

        if ($orderService->getErrors()) {
            foreach($orderService->getErrors() as $error) {
                $event->addErrorMessage($error);
            }
        }

        if ($orderService->getSuccess()) {

            $event->setOrder($orderService->getOrder());

            $this->getCartService()
                ->removeItems()
                ->getSession()
                ->set('order_id', $orderService->getOrder()->getId());

            $event->setResponse(new JsonResponse([
                'success' => true,
                'order_id' => $orderService->getOrder()->getId(),
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
