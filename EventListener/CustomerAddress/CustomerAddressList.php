<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CustomerAddressList
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressList
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
    public function onCustomerAddressList(CoreEvent $event)
    {
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $url = $this->getRouter()->generate('cart_admin_customer_mass_delete');

        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete Addresses',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url'      => $url,
                'external' => 0,
            ],
        ]);

        $event->setReturnData('columns', [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => 1,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => 1,
            ],
            [
                'key' => 'city',
                'label' => 'City',
                'sort' => 1,
            ],
            [
                'key' => 'region',
                'label' => 'Region/State',
                'sort' => 1,
            ],
            [
                'key' => 'country_id',
                'label' => 'Country',
                'sort' => 1,
            ],
        ]);

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $event->setResponse($this->getThemeService()->render(
                    'frontend',
                    'CustomerAddress:index.html.twig',
                    $event->getReturnData()
                ));
                break;
        }
    }
}
