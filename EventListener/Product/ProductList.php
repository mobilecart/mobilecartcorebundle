<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductList
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductList
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
     */
    public function onProductList(CoreEvent $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

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

        if (isset($returnData['search'])) {
            $search = $returnData['search'];
            $sortBy = $search->getSortBy();
            $sortDir = $search->getSortDir();
            if ($sortBy) {
                foreach($returnData['columns'] as $k => $colData) {
                    if ($colData['key'] == $sortBy) {
                        $returnData['columns'][$k]['isActive'] = 1;
                        $returnData['columns'][$k]['direction'] = $sortDir;
                        break;
                    }
                }
            }
        }

        switch($event->getSection()) {
            case ($format == 'json'):
            case CoreEvent::SECTION_API:

                $response = new JsonResponse($returnData);

                break;
            case CoreEvent::SECTION_FRONTEND:

                if ($event->getCategory()) {

                    switch($event->getCategory()->getDisplayMode()) {
                        case EntityConstants::DISPLAY_TEMPLATE:

                            $response = $this->getThemeService()
                                ->render('frontend', $event->getCategory()->getCustomTemplate(), $returnData);

                            break;
                        case EntityConstants::DISPLAY_TEMPLATE_PRODUCTS:

                            $returnData['custom_template_html'] = $this->getThemeService()
                                ->render('frontend', $event->getCategory()->getCustomTemplate(), $returnData);

                            $template = $event->getTemplate()
                                ? $event->getTemplate()
                                : 'Product:index.html.twig';

                            $response = $this->getThemeService()
                                ->render('frontend', $template, $returnData);

                            break;
                        case EntityConstants::DISPLAY_PRODUCTS:

                            $template = $event->getTemplate()
                                ? $event->getTemplate()
                                : 'Product:index.html.twig';

                            $response = $this->getThemeService()
                                ->render('frontend', $template, $returnData);

                            break;
                        default:

                            $template = $event->getTemplate()
                                ? $event->getTemplate()
                                : 'Product:index.html.twig';

                            $response = $this->getThemeService()
                                ->render('frontend', $template, $returnData);

                            break;
                    }

                } else {

                    $template = $event->getTemplate()
                        ? $event->getTemplate()
                        : 'Product:index.html.twig';

                    $response = $this->getThemeService()
                        ->render('frontend', $template, $returnData);
                }

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

                $template = $event->getTemplate()
                    ? $event->getTemplate()
                    : 'Product:index.html.twig';

                $response = $this->getThemeService()
                    ->render('admin', $template, $returnData);

                break;
            default:

                break;
        }

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
