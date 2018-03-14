<?php

namespace MobileCart\CoreBundle\EventListener\ItemVarSet;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ItemVarSetList
 * @package MobileCart\CoreBundle\EventListener\ItemVarSet
 */
class ItemVarSetList
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
    public function onItemVarSetList(CoreEvent $event)
    {
        $event->setReturnData('mass_actions', [
            [
                'label'         => 'Delete Field Sets',
                'input_label'   => 'Confirm Mass-Delete ?',
                'input'         => 'mass_delete',
                'input_type'    => 'select',
                'input_options' => [
                    ['value' => 0, 'label' => 'No'],
                    ['value' => 1, 'label' => 'Yes'],
                ],
                'url' => $this->router->generate('cart_admin_item_var_set_mass_delete'),
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
                    'key' => 'name',
                    'label' => 'Name',
                    'sort' => true,
                ],
                [
                    'key' => 'object_type',
                    'label' => 'Object Type',
                    'sort' => true,
                ],
            ]);
        }

        if ($event->isJsonResponse()) {

            $event->setResponse(new JsonResponse($event->getReturnData()));

        } else {

            $event->setResponse($this->getThemeService()->renderAdmin(
                'ItemVarSet:index.html.twig',
                $event->getReturnData()
            ));

        }
    }
}
