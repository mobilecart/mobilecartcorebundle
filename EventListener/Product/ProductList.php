<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductList
{

    protected $router;

    protected $themeService;

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

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    public function getThemeService()
    {
        return $this->themeService;
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

    public function onProductList(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();

        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $response = '';

        $returnData['columns'] = [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => 1,
            ],
            [
                'key' => 'type',
                'label' => 'Type',
                'sort' => 1,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => 1,
            ],
            [
                'key' => 'sku',
                'label' => 'SKU',
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
                'key' => 'is_in_stock',
                'label' => 'In Stock',
                'sort' => 1,
            ],
            [
                'key' => 'created_at',
                'label' => 'Created At',
                'sort' => 1,
            ],
        ];

        switch($event->getSection()) {
            case ($format == 'json'):
            case CoreEvent::SECTION_API:

                $response = new JsonResponse($returnData);

                break;
            case CoreEvent::SECTION_FRONTEND:

                $response = $this->getThemeService()
                    ->render('frontend', 'Product:index.html.twig', $returnData);

                break;
            case CoreEvent::SECTION_BACKEND:

                $returnData['mass_actions'] = [
                    [
                        'label'         => 'Update Stock',
                        'input_label'   => 'In Stock',
                        'input'         => 'is_in_stock',
                        'input_type'    => 'select',
                        'input_options' => [
                            ['value' => 0, 'label' => 'No'],
                            ['value' => 1, 'label' => 'Yes'],
                        ],
                        'url' => $this->getRouter()->generate('cart_admin_product_mass_update'),
                        'external' => 0,
                    ],
                    [
                        'label'         => 'Update On Sale',
                        'input_label'   => 'On Sale',
                        'input'         => 'is_on_sale',
                        'input_type'    => 'select',
                        'input_options' => [
                            ['value' => 0, 'label' => 'No'],
                            ['value' => 1, 'label' => 'Yes'],
                        ],
                        'url'      => $this->getRouter()->generate('cart_admin_product_mass_update'),
                        'external' => 0,
                    ],
                    [
                        'label'         => 'Delete Products',
                        'input_label'   => 'Confirm Mass-Delete ?',
                        'input'         => 'mass_delete',
                        'input_type'    => 'select',
                        'input_options' => [
                            ['value' => 0, 'label' => 'No'],
                            ['value' => 1, 'label' => 'Yes'],
                        ],
                        'url'      => $this->getRouter()->generate('cart_admin_product_mass_delete'),
                        'external' => 0,
                    ],
                ];

                $response = $this->getThemeService()
                    ->render('admin', 'Product:index.html.twig', $returnData);

                break;
            default:

                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
