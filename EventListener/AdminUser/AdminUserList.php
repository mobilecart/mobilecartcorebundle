<?php

namespace MobileCart\CoreBundle\EventListener\AdminUser;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AdminUserList
 * @package MobileCart\CoreBundle\EventListener\AdminUser
 */
class AdminUserList
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
    public function onAdminUserList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete Admin Users',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->getRouter()->generate('cart_admin_admin_user_mass_delete'),
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
                'key' => 'firstname',
                'label' => 'First Name',
                'sort' => true,
            ],
            [
                'key' => 'lastname',
                'label' => 'Last Name',
                'sort' => true,
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'sort' => true,
            ],
        ]);

        switch($event->getRequestAccept()) {
            case CoreEvent::JSON:

                $event->setResponse(new JsonResponse($event->getReturnData()));

                break;
            default:

                $event->setResponse($this->getThemeService()->renderAdmin(
                    'AdminUser:index.html.twig',
                    $event->getReturnData()
                ));

                break;
        }
    }
}
