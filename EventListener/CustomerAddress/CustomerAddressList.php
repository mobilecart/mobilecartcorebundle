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
                'sort' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => true,
            ],
            [
                'key' => 'city',
                'label' => 'City',
                'sort' => true,
            ],
            [
                'key' => 'region',
                'label' => 'Region/State',
                'sort' => true,
            ],
            [
                'key' => 'country_id',
                'label' => 'Country',
                'sort' => true,
            ],
        ]);

        if ($event->isJsonResponse()) {
            $event->setResponse(new JsonResponse($event->getReturnData()));
        } else {

            if ($event->getSection() == CoreEvent::SECTION_BACKEND) {

                $event->setResponse($this->getThemeService()->renderAdmin(
                    'CustomerAddress:index.html.twig',
                    $event->getReturnData()
                ));

            } else {

                $event->setResponse($this->getThemeService()->renderFrontend(
                    'CustomerAddress:index.html.twig',
                    $event->getReturnData()
                ));

            }
        }
    }
}
