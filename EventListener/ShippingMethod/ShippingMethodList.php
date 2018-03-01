<?php

namespace MobileCart\CoreBundle\EventListener\ShippingMethod;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ShippingMethodList
 * @package MobileCart\CoreBundle\EventListener\ShippingMethod
 */
class ShippingMethodList
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
    public function onShippingMethodList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->getRouter()->generate('cart_admin_shipping_method_mass_delete'),
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
                'key' => 'company',
                'label' => 'Company',
                'sort' => true,
            ],
            [
                'key' => 'method',
                'label' => 'Method',
                'sort' => true,
            ],
        ]);

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:

                $event->setResponse(new JsonResponse($event->getReturnData()));

                break;
            default:

                $event->setResponse($this->getThemeService()->renderAdmin(
                    'ShippingMethod:index.html.twig',
                    $event->getReturnData()
                ));

                break;
        }
    }
}
