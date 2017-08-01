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

    protected $router;

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
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $orderId = $request->get('id', 0);
        $customer = $event->getCustomer();
        $typeSections = [];
        $returnData['template_sections'] = $typeSections;

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

        $returnData['order'] = $order;

        $response = $this->getThemeService()
            ->render('frontend', 'Customer:order.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
