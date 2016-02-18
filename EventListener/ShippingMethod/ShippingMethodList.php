<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Shipping\RateRequest;
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class ShippingMethodList
{

    protected $router;

    protected $shippingService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
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

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
    {
        return $this->shippingService;
    }

    public function onShippingMethodList(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $search = new ArrayWrapper([
            'formats' => [
                'html' => 'HTML',
                'xml'  => 'XML',
                'json' => 'JSON',
                'csv'  => 'CSV',
            ],
            'page' => 1,
            'object_type' => 'shipping_method',
        ]);

        // todo : check request for format

        $search->set('format', 'html');

        $rateRequest = new RateRequest();
        $rateRequest->set('include_all', 1);
        $rateRequest->set('to_array', 0);

        $rates = $this->getShippingService()
            ->collectShippingRates($rateRequest);

        $result = new ArrayWrapper([
            'pages'    => 1,
            'entities' => $rates,
            'total'    => count($rates),
            'offset'   => 0,
        ]);

        // Data for Template, etc

        $returnData['search'] = $search;
        $returnData['result'] = $result;

        $returnData['mass_actions'] =
        [
            [
                'label'         => 'Delete',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->router->generate('cart_admin_shipping_method_mass_delete'),
                'external' => 0,
            ],
        ];

        $returnData['columns'] =
        [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => 0,
            ],
            [
                'key' => 'company',
                'label' => 'Company',
                'sort' => 0,
            ],
            [
                'key' => 'method',
                'label' => 'Method',
                'sort' => 0,
            ],
        ];

        $event->setReturnData($returnData);
    }
}
