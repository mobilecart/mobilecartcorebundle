<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CustomerOrderReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerOrderReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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

    /**
     * @param CoreEvent $event
     */
    public function onCustomerOrderReturn(CoreEvent $event)
    {
        $request = $event->getRequest();
        $orderId = $request->get('id', 0);
        $customer = $event->getCustomer();
        $event->setReturnData('template_sections', []);

        $order = $this->getEntityService()->find(EntityConstants::ORDER, $orderId);
        if (!$order
            || !$order->getCustomer()
            || !$order->getCustomer()->getId()
            || $order->getCustomer()->getId() != $customer->getId()
        ) {
            // redirect to order listing
            $url = $this->getRouter()->generate('customer_orders', []);
            $event->setResponse(new RedirectResponse($url));
            return;
        }

        $event->setReturnData('order', $order);

        $event->setResponse($this->getThemeService()->render(
            'frontend',
            'Customer:order.html.twig',
            $event->getReturnData()
        ));
    }
}
