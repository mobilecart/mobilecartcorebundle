<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CustomerList
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerList
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
    public function onCustomerList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete Customers',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url'      => $this->getRouter()->generate('cart_admin_customer_mass_delete'),
                'external' => 0,
            ],
        ]);

        // allow a previous listener to define the columns
        if (!$event->getReturnData('columns', [])) {

            $event->setReturnData('columns', [
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sort' => true,
                ],
                [
                    'key' => 'billing_firstname',
                    'label' => 'First Name',
                    'sort' => true,
                ],
                [
                    'key' => 'billing_lastname',
                    'label' => 'Last Name',
                    'sort' => true,
                ],
                [
                    'key' => 'email',
                    'label' => 'Email',
                    'sort' => true,
                ],
                [
                    'key' => 'created_at',
                    'label' => 'Created At',
                    'sort' => true,
                ],
            ]);
        }

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse($event->getReturnData()));

        } else {

            $event->setResponse($this->getThemeService()->renderAdmin(
                'Customer:index.html.twig',
                $event->getReturnData()
            ));
        }
    }
}
