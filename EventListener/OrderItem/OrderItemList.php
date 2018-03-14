<?php

namespace MobileCart\CoreBundle\EventListener\OrderItem;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class OrderItemList
 * @package MobileCart\CoreBundle\EventListener\OrderItem
 */
class OrderItemList
{
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
     * @param $router
     * @return $this
     */
    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderItemList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', []);

        // allow a previous listener to define the columns
        if (!$event->getReturnData('columns', [])) {

            $event->setReturnData('columns', [
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sort' => true,
                ],
                [
                    'key' => 'reference_nbr',
                    'label' => 'Order #',
                    'sort' => true,
                ],
                [
                    'key' => 'sku',
                    'label' => 'SKU',
                    'sort' => true,
                ],
                [
                    'key' => 'name',
                    'label' => 'Name',
                    'sort' => true,
                ],
                [
                    'key' => 'price',
                    'label' => 'Price',
                    'sort' => true,
                ],
                [
                    'key' => 'qty',
                    'label' => 'Qty',
                    'sort' => true,
                ],
                [
                    'key' => 'shipping_method',
                    'label' => 'Shipping',
                    'sort' => true,
                ],
                [
                    'key' => 'order_created_at',
                    'label' => 'Created At',
                    'sort' => true,
                ],
            ]);
        }

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse($event->getReturnData()));

        } else {

            $event->setResponse($this->getThemeService()->renderAdmin(
                'OrderItem:index.html.twig',
                $event->getReturnData()
            ));
        }
    }
}
