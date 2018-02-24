<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerOrderReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerOrderReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
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
        $order = $this->getEntityService()->findOneBy(EntityConstants::ORDER, [
            'id' => $event->getRequest()->get('id', 0),
            'customer' => $event->getCustomer()->getId()
        ]);

        if ($order) {
            if ($event->isJsonResponse()) {
                $event->setResponse(new JsonResponse([
                    'success' => true,
                    'entity' => $order->getData(),
                ]));
            } else {

                $event->setReturnData('template_sections', []);
                $event->setReturnData('order', $order);

                $event->setResponse($this->getThemeService()->render(
                    'frontend',
                    'Customer:order.html.twig',
                    $event->getReturnData()
                ));
            }
        } else {
            // redirect to order listing
            $event->addErrorMessage('Order not found');
            $event->flashMessages();
            $url = $this->getRouter()->generate('customer_orders', []);

            if ($event->isJsonResponse()) {
                $event->setResponse(new JsonResponse([
                    'success' => false,
                    'redirect_url' => $url,
                    'messages' => $event->getMessages(),
                ]));
            } else {
                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
}
