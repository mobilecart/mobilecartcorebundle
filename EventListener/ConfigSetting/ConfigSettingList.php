<?php

namespace MobileCart\CoreBundle\EventListener\ConfigSetting;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ConfigSettingList
 * @package MobileCart\CoreBundle\EventListener\ConfigSetting
 */
class ConfigSettingList
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
    public function onConfigSettingList(CoreEvent $event)
    {
        // allow a previous listener to define the columns
        if (!$event->getReturnData('columns', [])) {

            $event->setReturnData('columns', [
                [
                    'key' => 'id',
                    'label' => 'ID',
                    'sort' => true,
                ],
                [
                    'key' => 'label',
                    'label' => 'Label',
                    'sort' => true,
                ],
                [
                    'key' => 'code',
                    'label' => 'Code',
                    'sort' => true,
                ],
                [
                    'key' => 'value',
                    'label' => 'Value',
                    'sort' => true,
                ],
            ]);
        }

        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete Config Settings',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->getRouter()->generate('cart_admin_config_setting_mass_delete'),
                'external' => 0,
            ],
        ]);

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse($event->getReturnData()));

        } else {

            $event->setResponse($this->getThemeService()->renderAdmin(
                'ConfigSetting:index.html.twig',
                $event->getReturnData()
            ));
        }
    }
}
