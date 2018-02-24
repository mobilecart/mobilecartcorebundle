<?php

namespace MobileCart\CoreBundle\EventListener\Product;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductList
 * @package MobileCart\CoreBundle\EventListener\Product
 */
class ProductList
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
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
     * @param CoreEvent $event
     */
    public function onProductList(CoreEvent $event)
    {
        $event->setReturnData('columns', [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => true,
            ],
            [
                'key' => 'type',
                'label' => 'Type',
                'sort' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => true,
            ],
            [
                'key' => 'sku',
                'label' => 'SKU',
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
                'key' => 'is_in_stock',
                'label' => 'In Stock',
                'sort' => true,
            ],
            [
                'key' => 'created_at',
                'label' => 'Created At',
                'sort' => true,
            ],
        ]);

        if ($event->isJsonResponse()) {
            $event->setResponse(new JsonResponse($event->getReturnData()));
        } else {
            switch($event->getSection()) {
                case CoreEvent::SECTION_FRONTEND:

                    if ($event->getCategory()) {

                        switch($event->getCategory()->getDisplayMode()) {
                            case EntityConstants::DISPLAY_TEMPLATE:

                                $event->setResponse($this->getThemeService()->render(
                                    'frontend',
                                    $event->getCategory()->getCustomTemplate(),
                                    $event->getReturnData()
                                ));

                                break;
                            case EntityConstants::DISPLAY_TEMPLATE_PRODUCTS:

                                $event->setReturnData('custom_template_html', $this->getThemeService()->render(
                                    'frontend',
                                    $event->getCategory()->getCustomTemplate(),
                                    $event->getReturnData()
                                ));

                                $template = $event->getTemplate()
                                    ? $event->getTemplate()
                                    : 'Product:index.html.twig';

                                $event->setResponse($this->getThemeService()->render(
                                    'frontend',
                                    $template,
                                    $event->getReturnData()
                                ));

                                break;
                            case EntityConstants::DISPLAY_PRODUCTS:

                                $template = $event->getTemplate()
                                    ? $event->getTemplate()
                                    : 'Product:index.html.twig';

                                $event->setResponse($this->getThemeService()->render(
                                    'frontend',
                                    $template,
                                    $event->getReturnData()
                                ));

                                break;
                            default:

                                $template = $event->getTemplate()
                                    ? $event->getTemplate()
                                    : 'Product:index.html.twig';

                                $event->setResponse($this->getThemeService()->render(
                                    'frontend',
                                    $template,
                                    $event->getReturnData()
                                ));

                                break;
                        }

                    } else {

                        $template = $event->getTemplate()
                            ? $event->getTemplate()
                            : 'Product:index.html.twig';

                        $event->setResponse($this->getThemeService()->render(
                            'frontend',
                            $template,
                            $event->getReturnData()
                        ));
                    }

                    break;
                case CoreEvent::SECTION_BACKEND:

                    $event->setReturnData('mass_actions', [
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
                    ]);

                    $template = $event->getTemplate()
                        ? $event->getTemplate()
                        : 'Product:index.html.twig';

                    $event->setResponse($this->getThemeService()->render(
                        'admin',
                        $template,
                        $event->getReturnData()
                    ));

                    break;
                default:

                    break;
            }
        }
    }
}
