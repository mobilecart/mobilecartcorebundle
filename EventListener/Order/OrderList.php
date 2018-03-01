<?php

namespace MobileCart\CoreBundle\EventListener\Order;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class OrderList
 * @package MobileCart\CoreBundle\EventListener\Order
 */
class OrderList
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
     * @param \MobileCart\CoreBundle\Service\ThemeService $themeService
     * @return $this
     */
    public function setThemeService(\MobileCart\CoreBundle\Service\ThemeService $themeService)
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
    public function onOrderList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete Orders',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url'      => $this->getRouter()->generate('cart_admin_order_mass_delete'),
                'external' => 0,
            ],
        ]);

        $event->setReturnData('columns', [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => true,
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'sort' => true,
            ],
            [
                'key' => 'billing_name',
                'label' => 'Customer',
                'sort' => true,
            ],
            [
                'key' => 'total',
                'label' => 'Total',
                'sort' => true,
            ],
            [
                'key' => 'created_at',
                'label' => 'Created At',
                'sort' => true,
            ],
        ]);

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:

                $event->setResponse(new JsonResponse($event->getReturnData()));

                break;
            default:

                $event->setResponse($this->getThemeService()->renderAdmin(
                    'Order:index.html.twig',
                    $event->getReturnData()
                ));

                break;
        }
    }
}
