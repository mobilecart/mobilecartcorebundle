<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CheckoutSuccessReturn
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutSuccessReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

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
     * @param CoreEvent $event
     * @return bool
     */
    public function onCheckoutSuccessReturn(CoreEvent $event)
    {
        $orderId = $this->getCheckoutSessionService()->getCartSessionService()->getSession()->get('order_id', 0);
        if (!$orderId) {
            // redirect to checkout page
            $url = $this->getRouter()->generate('cart_checkout', []);
            $response = new RedirectResponse($url);
            $event->setResponse($response);
            return false;
        }

        $returnData = $event->getReturnData();

        // get cart customer
        $cartCustomer = $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->getCart()
            ->getCustomer();

        // clear cart and set customer
        $this->getCheckoutSessionService()
            ->getCartSessionService()
            ->resetCart()
            ->setCustomer($cartCustomer);

        $order = $this->getEntityService()->find(EntityConstants::ORDER, $orderId);

        $returnData['order'] = $order;

        $response = $this->getThemeService()
            ->render('frontend', 'Checkout:success.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
