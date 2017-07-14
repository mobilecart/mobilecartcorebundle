<?php

namespace MobileCart\CoreBundle\EventListener\OrderItem;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
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
     * @param Event $event
     */
    public function onOrderItemList(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $response = '';
        $returnData['mass_actions'] = [];

        $returnData['columns'] =
        [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => 1,
            ],
            [
                'key' => 'reference_nbr',
                'label' => 'Order #',
                'sort' => 1,
            ],
            [
                'key' => 'sku',
                'label' => 'SKU',
                'sort' => 1,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => 1,
            ],
            [
                'key' => 'price',
                'label' => 'Price',
                'sort' => 1,
            ],
            [
                'key' => 'qty',
                'label' => 'Qty',
                'sort' => 1,
            ],
            [
                'key' => 'shipping_method',
                'label' => 'Shipping',
                'sort' => 1,
            ],
            [
                'key' => 'order_created_at',
                'label' => 'Created At',
                'sort' => 1,
            ],
        ];

        switch($format) {
            case 'json':
                $response = new JsonResponse($returnData);
                break;
            default:

                $response = $this->getThemeService()
                    ->render('admin', 'OrderItem:index.html.twig', $returnData);

                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
