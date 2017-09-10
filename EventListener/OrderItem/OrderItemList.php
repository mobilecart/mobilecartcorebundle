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
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $event->setReturnData('mass_actions', []);

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

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $event->setResponse($this->getThemeService()->render(
                    'admin',
                    'OrderItem:index.html.twig',
                    $event->getReturnData()
                ));
                break;
        }
    }
}
