<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CheckoutSuccessReturn
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutSuccessReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

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
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
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
    public function onCheckoutSuccessReturn(CoreEvent $event)
    {
        $orderId = $this->getCartService()->getSession()->get('order_id', 0);
        if (!$orderId) {
            // redirect to checkout page
            $url = $this->getRouter()->generate('cart_checkout', []);
            $response = new RedirectResponse($url);
            $event->setResponse($response);
            return false;
        }

        // get cart customer
        $cartCustomer = $this->getCartService()
            ->getCart()
            ->getCustomer();

        // clear cart and set customer
        $this->getCartService()
            ->resetCart()
            ->setCustomer($cartCustomer);

        $order = $this->getEntityService()->find(EntityConstants::ORDER, $orderId);

        $event->setReturnData('order', $order);

        $event->setResponse($this->getThemeService()->render(
            'frontend',
            'Checkout:success.html.twig',
            $event->getReturnData()
        ));
    }
}
